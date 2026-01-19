<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging;

use League\Csv\Writer;
use Psr\Log\LoggerInterface;
use RCS\PDF\PDFService;
use RCS\PDF\PDFServiceException;
use RCS\Util\FileSystem;
use WSCL\Main\Staging\Entity\RacePlateRcd;
use WSCL\Main\Staging\Entity\RaceResultExportRcd;
use WSCL\Main\Staging\Entity\Rider;
use WSCL\Main\Staging\Entity\RiderRacePlateRcd;
use WSCL\Main\Staging\Entity\TeamEnvelopeRcd;
use WSCL\Main\Staging\Entity\TimingRcd;
use WSCL\Main\Staging\Models\Category;
use WSCL\Main\Staging\Models\Event;
use WSCL\Main\Staging\Models\Race;
use WSCL\Main\Staging\Models\StagingLink;
use WSCL\Main\Staging\Types\CategoryMap;
use WSCL\Main\Staging\Types\RegisteredRiderMap;
use WSCL\Main\Staging\Types\RiderByCategorySet;
use WSCL\Main\Staging\Types\RiderByLastFirstNameSet;
use WSCL\Main\Staging\Types\RiderByPlateSet;
use WSCL\Main\Staging\Types\RiderBySet;
use WSCL\Main\Staging\Types\RiderByTeamAndRaceMap;
use WSCL\Main\Staging\Types\TeamEnvelopeMap;
use WSCL\Main\Staging\Types\TeamSizeMap;
use WSCL\Main\Staging\Types\TimingRiderMap;
use RCS\WP\PluginInfoInterface;
use WSCL\Main\WsclMainOptionsInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use WSCL\Main\RaceResult\RaceResultClient;

class StagingApp
{
    private const RIDERS_BY_NAME_XML = "RidersByName.xml";
    private const RIDERS_BY_CATEGORY_XML = "RidersByCategory.xml";
    private const STAGING_SHEETS_XML = "StagingSheets.xml";
    private const STAGING_SUMMARY_XML = "StagingSummary.xml";
    private const TEAM_STAGING_XML = "TeamStaging.xml";

    private const STAGING_ORDER_XSLT = "StagingOrder.xslt";
    private const STAGING_SHEETS_XSLT = "StagingSheets.xslt";
    private const STAGING_SUMMARY_XSLT = "StagingSummary.xslt";
    private const TEAM_STAGING_XSLT = "TeamStaging.xslt";
    private const COMMON_XSLT = "Common.xslt";
    private const LOGO_PNG = "WSCL_Logo.png";

    private const STAGING_ORDER_BY_NAME_PDF = "StagingOrderByName.pdf";
    private const STAGING_ORDER_BY_CATEGORY_PDF = "StagingOrderByCategory.pdf";
    private const STAGING_SHEETS_PDF = "StagingSheets.pdf";
    private const STAGING_SUMMARY_PDF = "StagingSummary.pdf";
    private const TEAM_STAGING_PDF = "TeamStaging.pdf";

    private const TIMING_SYSTEM_IMPORT_CSV = "TimingSystemImport.csv";
    private const RACE_PLATE_DATA_CSV = "RacePlatesDatabase.csv";
    private const TEAM_ENVELOPE_DATA_CSV = "TeamEnvelopesDatabase.csv";

    private \DateTime $runTimestamp;
    private String $runTimestampStr;

    public function __construct(
        private PluginInfoInterface $pluginInfo,
        private WsclMainOptionsInterface $options,
        private RaceResultClient $rrClient,
        private LoggerInterface $logger,
        private BgProcessInterface $bgProcess
        )
    {
        $this->runTimestamp = new \DateTime("now", new \DateTimeZone('America/Los_Angeles'));
        $this->runTimestampStr = $this->runTimestamp->format("Y/m/d H:i:s");
    }

    /**
     *
     * @param Event $event
     * @param string $regFile
     *
     * @return StagingLink[]
     */
    public function generateStaging(
        Event $event,
        string $regFile
        ): array
    {
        $result = [];

        $wpUploadDir = wp_upload_dir()['basedir'];
        $wpUploadUrl = wp_upload_dir()['baseurl'];

        $outputDir = $this->pluginInfo->getWriteDir() . '/Staging/' . str_replace(' ', '_', $event->getName()) . '/';
        $tmpDir = $outputDir . 'tmp/';      // temporary directory where we'll put anything we need temporarily.

        // Delete the content of the output directory if it exists
        is_dir($outputDir) && FileSystem::purgeFolder($outputDir);
        !is_dir($tmpDir) && mkdir($tmpDir, 0755, true);  // Create the temp directory recursively

        $regLoader = new RegistrationLoader($this->logger, $this->bgProcess);
        $regLoader->loadRegistrationFile($regFile);

        if ($regLoader->isMissingData()) {
            throw new \DomainException('Missing required data in registration file.', 400);
        } else {
            $teamSizeMap = $this->initializeTeamSizeMap($regLoader->getRiderMap(), $event->getDivisionCnt());

            $timingLoader = new TimingLoader($this->rrClient, $this->logger);
            $timingLoader->loadTimingFiles($regLoader->getRiderMap(), $event);

            $categoryMap = new CategoryMap();
            $riderByCategory = new RiderByCategorySet();
            $riderByName = new RiderByLastFirstNameSet();
            $riderByTeamAndRace = new RiderByTeamAndRaceMap($event);

            $this->initializeRiders(
                $regLoader->getRiderMap(),
                $timingLoader->getRiderMap(),
                $categoryMap,
                $riderByCategory,
                $riderByName,
                $riderByTeamAndRace,
                $teamSizeMap
                );

            if ($event->seasonFirstEvent) {
                $extraPlateSet = new RiderByPlateSet();
                $this->addSweepPlates($extraPlateSet, $event->categories);
                $this->addRoverPlates($extraPlateSet);
                $this->addReplacementPlates($extraPlateSet);

                $racePlateCsvFile = $outputDir . self::RACE_PLATE_DATA_CSV;
                $this->generateRacePlateDataCsv($riderByName, $extraPlateSet, $racePlateCsvFile);
                $result[] = new StagingLink(
                    'Race Plate CSV',
                    $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $racePlateCsvFile),
                    mime_content_type($racePlateCsvFile)
                    );

                $teamEnvelopeCsvFile = $outputDir . self::TEAM_ENVELOPE_DATA_CSV;
                $this->generateTeamEnvelopeDataCsv($riderByName, $teamEnvelopeCsvFile);
                $result[] = new StagingLink(
                    'Team Envelopes CSV',
                    $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $teamEnvelopeCsvFile),
                    mime_content_type($teamEnvelopeCsvFile)
                    );
            }

            if (!$categoryMap->isEmpty()) {
                $this->initializeRows($event, $categoryMap);

                $stagingSheetsPdfFile = $outputDir . self::STAGING_SHEETS_PDF;
                $this->generateStagingSheetsPdf($event, $tmpDir, $stagingSheetsPdfFile);
                $result[] = new StagingLink(
                    'Staging Sheets PDF',
                    $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $stagingSheetsPdfFile),
                    mime_content_type($stagingSheetsPdfFile)
                    );

                $teamStagingPdfFile = $outputDir . self::TEAM_STAGING_PDF;
                $this->generateTeamStagingPdf($event, $riderByTeamAndRace, $tmpDir, $teamStagingPdfFile);
                $result[] = new StagingLink(
                    'Team Staging PDF',
                    $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $teamStagingPdfFile),
                    mime_content_type($teamStagingPdfFile)
                    );

                $stagingSummaryPdfFile = $outputDir . self::STAGING_SUMMARY_PDF;
                $this->generateStagingSummaryPdf($event, $tmpDir, $stagingSummaryPdfFile);
                $result[] = new StagingLink(
                    'Staging Summary PDF',
                    $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $stagingSummaryPdfFile),
                    mime_content_type($stagingSummaryPdfFile)
                    );

                $timingSystemImportCsvFile = $outputDir . self::TIMING_SYSTEM_IMPORT_CSV;
                $this->generateTimingSystemImportCsv($riderByName, $timingSystemImportCsvFile);
                $result[] = new StagingLink(
                    'Timing System Import CSV',
                    $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $timingSystemImportCsvFile),
                    mime_content_type($timingSystemImportCsvFile)
                    );

                $ridersByNameXmlFile = $tmpDir . self::RIDERS_BY_NAME_XML;
                $stagingOrderByNamePdfFile = $outputDir . self::STAGING_ORDER_BY_NAME_PDF;
                $this->generateStagingOrderPdf(
                    $event,
                    $riderByName,
                    $ridersByNameXmlFile,
                    $stagingOrderByNamePdfFile,
                    "Lastname/Firstname"
                    );
                $result[] = new StagingLink(
                    'Riders By Name PDF',
                    $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $stagingOrderByNamePdfFile),
                    mime_content_type($stagingOrderByNamePdfFile)
                    );

                $ridersByCategoryXmlFile = $tmpDir . self::RIDERS_BY_CATEGORY_XML;
                $stagingOderByCategoryPdfFile = $outputDir . self::STAGING_ORDER_BY_CATEGORY_PDF;
                $this->generateStagingOrderPdf(
                    $event,
                    $riderByCategory,
                    $ridersByCategoryXmlFile,
                    $stagingOderByCategoryPdfFile,
                    "Category"
                    );
                $result[] = new StagingLink(
                    'Riders By Category PDF',
                    $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $stagingOderByCategoryPdfFile),
                    mime_content_type($stagingOderByCategoryPdfFile)
                    );
            }
        }

        return $result;
    }

    private function initializeTeamSizeMap(RegisteredRiderMap $regRiderMap, int $divisionCnt): TeamSizeMap
    {
        $teamSizeMap = new TeamSizeMap($divisionCnt);

        foreach ($regRiderMap->values() as $rider) {
            $teamSizeMap->addRider($rider->getTeam(), $rider);
        }

        $teamSizeMap->initializeDivisions();

        return $teamSizeMap;
    }

    /**
     * Loads the input file, expected to be a CSV file containting CCNRider information.
     *
     * @param RegisteredRiderMap $regRiderMap
     * @param TimingRiderMap $timingRiderMap
     * @param CategoryMap $categoryMap
     * @param RiderByCategorySet $riderByCategory
     * @param RiderByLastFirstNameSet $riderByName
     * @param RiderByTeamAndRaceMap $riderByTeamAndRace
     * @param TeamSizeMap $teamSizeMap
     */
    private function initializeRiders(
        RegisteredRiderMap $regRiderMap,
        TimingRiderMap $timingRiderMap,
        CategoryMap $categoryMap,
        RiderByCategorySet $riderByCategory,
        RiderByLastFirstNameSet $riderByName,
        RiderByTeamAndRaceMap $riderByTeamAndRace,
        TeamSizeMap $teamSizeMap
        ): void
    {
        foreach ($regRiderMap->values() as $regRcd) {
            /** @var TimingRcd|NULL */
            $timingRcd = $timingRiderMap->get($regRcd);

            if (!is_null($timingRcd)) {
                /** @var Rider */
                $rider = new Rider($regRcd, $timingRcd, $teamSizeMap);

                $categoryMap->addRider($regRcd->getCategory(), $rider);
                $riderByCategory->add($rider);
                $riderByName->add($rider);
                $riderByTeamAndRace->add($rider);
            }
        }
    }

    private function generateRacePlateDataCsv(RiderBySet $riderSet, RiderByPlateSet $extrasSet, string $csvFile): void
    {
        $writer = Writer::createFromPath($csvFile, "w");

        $headerNames = RacePlateRcd::getColumnNames();

        $writer->insertOne($headerNames);

        $riderPlateSet = new RiderByPlateSet();

        // Create set of RacePlateRcds
        foreach ($riderSet->getEntries() as $rider) {
            $riderPlateSet->add(new RiderRacePlateRcd($rider));
        }

        // Write the (sorted) set to the file
        foreach ($riderPlateSet->getEntries() as $rrRider) {
            $values = RacePlateRcd::getColumnValues($rrRider);
            $writer->insertOne($values);
        }

        foreach ($extrasSet->getEntries() as $rrRider) {
            $values = RacePlateRcd::getColumnValues($rrRider);
            $writer->insertOne($values);
        }
    }

    private function generateTeamEnvelopeDataCsv(RiderBySet $riderSet, string $csvFile): void
    {
        $writer = Writer::createFromPath($csvFile, "w");

        $headerNames = TeamEnvelopeRcd::getColumnNames();

        $writer->insertOne($headerNames);

        $teamEnvelopeMap = new TeamEnvelopeMap();

        // Create set of TeamEnvelopeRcds
        foreach ($riderSet->getEntries() as $rider) {
            $teamEnvelopeMap->add($rider);
        }

        // Write the (sorted) set to the file
        foreach ($teamEnvelopeMap->getEntries() as $teamRcd) {
            $values = TeamEnvelopeRcd::getColumnValues($teamRcd);
            $writer->insertOne($values);
        }
    }

    private function initializeRows(Event $event, CategoryMap $categoryMap): void
    {
        $totalRiderCnt = 0;

        foreach ($event->getRaces() as $race) {
            $raceCnt = 0;
            $curRow = 1;

            foreach ($event->getRaceCategories($race->getId()) as $category) {
                /** @var Rider[] */
                $riderSet = $categoryMap->getRidersByCategory($category->getName());

                if (!empty($riderSet)) {
                    $ridersInRow = 0;
                    $doubleUp = false;

                    foreach ($riderSet as $rider) {
                        $this->initializeRiderRow($rider, $event, $ridersInRow, $curRow, $doubleUp);
                    }

                    $category->initializeWaves($riderSet, $event->getRidersPerRow(), $event->getAttendanceFactor());
                    $raceCnt += count($riderSet);

                    if (0 != $ridersInRow) {    // did we end up with somebody in the last row?
                        $curRow++;    // move to next row for next category
                    }
                }
            }

            $totalRiderCnt += $raceCnt;
            $race->setRiderCnt($raceCnt);
        }
        $event->setRiderCnt($totalRiderCnt);
    }

    private function initializeRiderRow(
        Rider $rider,
        Event $event,
        int &$ridersInRow,
        int &$curRow,
        bool &$doubleUp
        ): void
    {
        $rider->setStagingRow($curRow);
        $ridersInRow++;

        if ($ridersInRow == $event->getRidersPerRow() * ($doubleUp ? 2 : 1)) {
            $curRow++;
            $ridersInRow = 0;

            if (!$doubleUp && $rider->getStagingScore() <= $event->getDoubleUpRowsPoints()) {
                $doubleUp = true;
            }
        }
    }

    private function generateStagingSheetsPdf(Event $event, string $xmlDir, string $pdfFile): void
    {
        $xmlFile = $xmlDir . self::STAGING_SHEETS_XML;

        $writer = new \XMLWriter();
        if ($writer->openUri($xmlFile)) {
            $writer->startDocument('1.0', 'utf-8');
            $writer->startElement('StagingSheets');

            $races = $event->getRaces();
            usort($races, fn(Race $r1, Race $r2) => $r1->getStartTime() <=> $r2->getStartTime());

            foreach ($races as $race) {
                foreach ($event->getRaceCategories($race->getId()) as $category) {
                    foreach ($category->getWaveLists() as $riders) {
                        if (!empty($riders)) {
                            $this->generateCategoryStagingSheetXML($event, $race, $category, $riders, $writer);
                        }
                    }
                }
            }
            $writer->endElement();      // StagingSheets

            $writer->endDocument();
        }
        $this->generatePDF($xmlFile, self::STAGING_SHEETS_XSLT, $pdfFile);
    }

    private function generateTeamStagingPdf(
        Event $event,
        RiderByTeamAndRaceMap $riderMap,
        string $xmlDir,
        string $pdfFile
        ): void
    {
        $xmlFile = $xmlDir . self::TEAM_STAGING_XML;

        $writer = new \XMLWriter();
        if ($writer->openUri($xmlFile)) {
            $writer->startDocument('1.0', 'utf-8');
            $writer->startElement('TeamStaging');
            $writer->writeAttribute('eventName', $event->getName());
            $writer->writeAttribute('eventDate', $event->getRaceDate()->format('m/d/Y'));
            $writer->writeAttribute('timestamp', $this->runTimestampStr);

            foreach ($riderMap->getTeamEntries() as $teamEntry) {
                $writer->startElement('Team');
                $writer->writeAttribute('name', $teamEntry->getTeam());

                foreach ($teamEntry->getRaces() as $teamRace) {
                    $writer->startElement('Race');
                    $writer->writeAttribute('time', $teamRace->getStartTime());

                    foreach ($teamRace->getRiders() as $rider) {
                        $writer->startElement('Rider');

                        $writer->writeElement('Bib', strval($rider->getBibNumber()));
                        $writer->writeElement('Name', sprintf('%s %s', $rider->getFirstName(), $rider->getLastName()));
                        $writer->writeElement('Category', $rider->getCategory());
                        $writer->writeElement('Row', strval($rider->getStagingRow()));

                        $writer->endElement();  // Rider
                    }
                    $writer->endElement();  // Race
                }
                $writer->endElement();  // Team
            }

            $writer->endElement();      // TeamStaging

            $writer->endDocument();
        }
        $this->generatePDF($xmlFile, self::TEAM_STAGING_XSLT, $pdfFile);
    }

    /**
     *
     * @param Event $event
     * @param Race $race
     * @param Category $category
     * @param Rider[] $riderList
     * @param \XMLWriter $writer
     */
    private function generateCategoryStagingSheetXML(
        Event $event,
        Race $race,
        Category $category,
        array $riderList,
        \XMLWriter $writer
        ): void
    {
        $writer->startElement('StagingSheet');

        $writer->writeElement('Timestamp', $this->runTimestampStr);

        $writer->startElement('ReportHeader');

        $writer->writeElement('EventName', $event->getName());
        $writer->writeElement('EventDate', $event->getRaceDateAsString());
        $writer->writeElement('Category', $category->getName());
        $writer->writeElement('Wave', current($riderList)->getWaveId());
        $writer->writeElement('StartTime', $race->getStartTimeAsString());
        $writer->writeElement('StagingTime', $race->getStagingTimeAsString());
        $writer->writeElement('RowWidth', strval($event->getRidersPerRow()));

        $writer->startElement('Instructions');

        $writer->writeElement(
            'Instruction',
            '1. At staging time, make sure all riders are in their assigned row.'
            );
        $writer->writeElement(
            'Instruction',
            '2. After everyone is in their row, fill vacant spots with riders from the row behind.'
            );
        $writer->writeElement(
            'Instruction',
            '3. IF A RIDER IS NOT PRESENT IN THEIR ASSIGNED ROW AT THE TIME OF STAGING, ' .
            'THEY SHOULD BE POSITIONED AT THE BACK OF THEIR WAVE/FIELD.'
            );
        $writer->writeElement(
            'Instruction',
            '4. After staging the riders, remain at the front of the group. Move forward ' .
            'with the group as each wave/category starts until you reach the start line.'
            );

        $writer->endElement();      // Instructions

        $writer->endElement();      // Report Header

        $writer->startElement('Rows');

        $lastRow = 0;

        foreach ($riderList as $rider) {
            if ($rider->getStagingRow() != $lastRow) {
                if (0 != $lastRow) {
                    $writer->endElement();    // StagingRow
                }
                $lastRow = $rider->getStagingRow();
                $writer->startElement('StagingRow');
                $writer->writeAttribute('number', strval($lastRow));
            }

            $writer->startElement('Rider');

            $writer->writeElement('Order', strval($rider->getStartOrder()));
            $writer->writeElement('Bib', strval($rider->getBibNumber()));
            $writer->writeElement('Firstname', $rider->getFirstName());
            $writer->writeElement('Lastname', $rider->getLastName());
            $writer->writeElement('Team', $rider->getTeam());
            $writer->writeElement('Points', strval($rider->getCurrentSeasonPoints()));
            $writer->writeElement('StagingScore', sprintf('%3.2f', $rider->getStagingScore()));

            $writer->endElement();  // Rider
        }
        $writer->endElement();      // StagingRow

        $writer->endElement();      // Rows

        $writer->endElement();      // StagingSheet
    }

    private function generateStagingSummaryPdf(Event $event, string $xmlDir, string $pdfFile): void
    {
        $xmlFile = $xmlDir . self::STAGING_SUMMARY_XML;

        $this->generateStagingSummaryXML($event, $xmlFile);

        $this->generatePDF($xmlFile, self::STAGING_SUMMARY_XSLT, $pdfFile);
    }

    private function generateStagingSummaryXML(Event $event, string $xmlFile): void
    {
        $writer = new \XMLWriter();
        if ($writer->openUri($xmlFile)) {
            $writer->startDocument('1.0', 'utf-8');
            $writer->startElement('StagingSummary');

            $writer->writeElement('Timestamp', $this->runTimestampStr);

            $writer->startElement('ReportHeader');

            $writer->writeElement('EventName', $event->getName());
            $writer->writeElement('EventDate', $event->getRaceDateAsString());

            $writer->endElement();      // Report Header

            $writer->startElement('Races');

            $writer->writeElement('TotalRiders', strval($event->getRiderCnt()));
            $writer->writeElement('RowWidth', strval($event->getRidersPerRow()));

            $races = $event->getRaces();
            usort($races, fn(Race $r1, Race $r2) => $r1->getStartTime() <=> $r2->getStartTime());

            foreach ($races as $race) {
                $rowCnt = 0;

                $writer->startElement('Race');

                $writer->writeElement('StartTime', $race->getStartTimeAsString());
                $writer->writeElement('Size', strval($race->getRiderCnt()));

                $writer->startElement('Categories');

                $waveCnt = 1;
                foreach ($event->getRaceCategories($race->getId()) as $category) {
                    $riderLists = $category->getWaveLists();
                    $waveID = 'A';

                    foreach ($riderLists as $riderList) {
                        $firstRow = $riderList[array_key_first($riderList)]->getStagingRow();
                        $lastRow = $riderList[array_key_last($riderList)]->getStagingRow();
                        $rowCnt = max($rowCnt, $lastRow);

                        $writer->startElement('Category');

                        $writer->writeElement('Wave', strval($waveCnt));
                        $writer->writeElement(
                            'Name',
                            $category->getName() . (1 == count($riderLists) ? '' : (' [' . $waveID . ']'))
                            );
                        $writer->writeElement('Size', strval(count($riderList)));
                        $writer->writeElement('FirstRow', strval($firstRow));
                        $writer->writeElement('LastRow', strval($lastRow));

                        $writer->endElement();  // Category

                        $waveCnt++;
                        $waveID++;
                    }
                }

                $writer->endElement();  // Categories

                $writer->writeElement('Rows', strval($rowCnt));

                $writer->endElement();  // Race
            }

            $writer->endElement();      // Races

            $writer->endElement();      // StagingSummary

            $writer->endDocument();
        }
    }

    /**
     *
     * @param RiderBySet $riderSet
     * @param string $csvFile
     */
    private function generateTimingSystemImportCsv(RiderBySet $riderSet, string $csvFile): void
    {
        $writer = Writer::createFromPath($csvFile, "w");

        $headerNames = RaceResultExportRcd::getColumnNames();

        $writer->insertOne($headerNames);

        foreach ($riderSet->getEntries() as $rider) {
            /** @var RaceResultExportRcd */
            $rrRider = (new RaceResultExportRcd())->fromRider($rider);

            $values = RaceResultExportRcd::getColumnValues($rrRider);
            $writer->insertOne($values);
        }
    }

    /**
     *
     * @param Event $event
     * @param RiderBySet $riderSet
     * @param string $xmlFile
     * @param string $pdfFile
     * @param string $sortOrder
     */
    private function generateStagingOrderPdf(
        Event $event,
        RiderBySet $riderSet,
        string $xmlFile,
        string $pdfFile,
        string $sortOrder
        ): void
    {
        $this->generateRiderStagingOrderXML(
            $riderSet,
            $event->getName(),
            $event->getRaceDateAsString(),
            $sortOrder,
            $xmlFile
            );

        $this->generatePDF($xmlFile, self::STAGING_ORDER_XSLT, $pdfFile);
    }

    /**
     *
     * @param RiderBySet $riderSet
     * @param string $eventName
     * @param string $eventDate
     * @param string $sortOrder
     * @param string $xmlFile
     */
    private function generateRiderStagingOrderXML(
        RiderBySet $riderSet,
        string $eventName,
        string $eventDate,
        string $sortOrder,
        string $xmlFile
        ): void
    {
        $writer = new \XMLWriter();
        if ($writer->openUri($xmlFile)) {
            $writer->startDocument('1.0', 'utf-8');
            $writer->startElement("StagingOrder");

            $writer->writeElement('Timestamp', $this->runTimestampStr);

            $writer->startElement('ReportHeader');

            $writer->writeElement('EventName', $eventName);
            $writer->writeElement('EventDate', $eventDate);
            $writer->writeElement('SortOrder', $sortOrder);

            $writer->endElement();

            $writer->startElement('Riders');

            foreach ($riderSet->getEntries() as $rider) {
                $writer->startElement('Rider');

                $writer->writeElement('Bib', strval($rider->getBibNumber()));
                $writer->writeElement('Firstname', $rider->getFirstName());
                $writer->writeElement('Lastname', $rider->getLastName());
                $writer->writeElement('Grade', strval($rider->getGrade()));
                $writer->writeElement('Gender', $rider->getGender());
                $writer->writeElement('Team', $rider->getTeam());
                $writer->writeElement('Category', $rider->getCategory());
                $writer->writeElement('Wave', $rider->getWaveId());
                $writer->writeElement('StagingRow', strval($rider->getStagingRow()));
                $writer->writeElement('Points', strval($rider->getCurrentSeasonPoints()));
                $writer->writeElement('StagingScore', sprintf('%3.2f', $rider->getStagingScore()));

                $writer->endElement();  // Rider
            }
            $writer->endElement();      // Riders

            $writer->endElement();      // RidersByName
            $writer->endDocument();
        }
    }

    private function generatePDF(string $xmlFile, string $xsltFile, string $pdfFile): void
    {
        $pluginPath = trailingslashit(WP_PLUGIN_DIR) . $this->pluginInfo->getSlug();
        $pdfResources = $pluginPath . '/resources/';

        $supportFiles = array();
        array_push($supportFiles, $pdfResources . self::COMMON_XSLT);
        array_push($supportFiles, $pdfResources . self::LOGO_PNG);

        try {
            $pdfSvc = new PDFService($this->options->getPdfServiceUrl(), $this->logger);
            $pdfSvc->fetchPDF($xmlFile, $pdfResources . $xsltFile, $pdfFile, $supportFiles);
        } catch (PDFServiceException $pse) {
            throw new \DomainException($pse->getMessage(), 400);
        }
    }

    private function replaceWPUploadDirWithUrl(string $wpUploadDir, string $wpUploadUrl, string $filename): string
    {
        return str_replace($wpUploadDir, $wpUploadUrl, $filename);
    }

    const SWEEP_ROVER_CATEGORY = 'Sweep/Rover';
    const SWEEP_PLATES_PER_CATEGORY = 3;

    /**
     *
     * @param Category[] $categories
     */
    private function addSweepPlates(RiderByPlateSet $plateSet, array $categories): void
    {
        $sweepBib = 5001;
        foreach ($categories as $category) {
            // Skip plates if the sweep doesn't have an abbreviation. Like a Varsity category
            if (isset($category->plateAbbreviation) && 0 < strlen(trim($category->plateAbbreviation))) {
                for ($ndx = 0; $ndx < self::SWEEP_PLATES_PER_CATEGORY; $ndx++) {
                    $plate = new RacePlateRcd();
                    $plate->bib = $sweepBib;
                    $plate->plateName = sprintf('%s Sweep', $category->plateAbbreviation);
                    $plate->fullName = sprintf('%s Sweep', $category->name);
                    $plate->raceCategory = self::SWEEP_ROVER_CATEGORY;

                    $plateSet->add($plate);
                    $sweepBib++;
                }
            }
        }
    }

    private function addRoverPlates(RiderByPlateSet $plateSet): void
    {
        foreach (range(9001, 9010) as $bib) {
            $plate = new RacePlateRcd();
            $plate->bib = $bib;
            $plate->plateName = 'ROVER';
            $plate->fullName = 'Rover Sweep';
            $plate->raceCategory = self::SWEEP_ROVER_CATEGORY;

            $plateSet->add($plate);
        }
    }

    private function addReplacementPlates(RiderByPlateSet $plateSet): void
    {
        foreach (range(1001, 1050) as $bib) {
            $plate = new RacePlateRcd();
            $plate->bib = $bib;
            $plate->plateName = '';
            $plate->fullName = '';
            $plate->raceCategory = '';

            $plateSet->add($plate);
        }
    }
}
