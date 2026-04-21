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
use WSCL\Main\Staging\Types\RiderByBibSet;
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
    private const DIVISION_LIST_XML = "DivisionList.xml";

    private const STAGING_ORDER_XSLT = "StagingOrder.xslt";
    private const STAGING_SHEETS_XSLT = "StagingSheets.xslt";
    private const STAGING_SUMMARY_XSLT = "StagingSummary.xslt";
    private const TEAM_STAGING_XSLT = "TeamStaging.xslt";
    private const DIVISION_LIST_XSLT = "DivisionList.xslt";
    private const COMMON_XSLT = "Common.xslt";
    private const LOGO_PNG = "WSCL_Logo.png";

    private const STAGING_ORDER_BY_NAME_PDF = "StagingOrderByName.pdf";
    private const STAGING_ORDER_BY_CATEGORY_PDF = "StagingOrderByCategory.pdf";
    private const STAGING_ORDER_BY_BIB_PDF = "StagingOrderByBib.pdf";
    private const STAGING_SHEETS_PDF = "StagingSheets.pdf";
    private const STAGING_SUMMARY_PDF = "StagingSummary.pdf";
    private const TEAM_STAGING_PDF = "TeamStaging.pdf";
    private const DIVISION_LIST_PDF = "DivisionsList.pdf";

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
        $outputDir = $this->getOutputDir($event);
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
            $riderByBib = new RiderByBibSet();
            $riderByTeamAndRace = new RiderByTeamAndRaceMap($event);

            $this->initializeRiders(
                $regLoader->getRiderMap(),
                $timingLoader->getRiderMap(),
                $categoryMap,
                [
                    $riderByCategory,
                    $riderByName,
                    $riderByBib
                ],
                $riderByTeamAndRace,
                $teamSizeMap
                );

            if ($event->seasonFirstEvent) {
                $extraPlateSet = new RiderByPlateSet();
                $this->addSweepPlates($extraPlateSet, $event->categories);
                $this->addRoverPlates($extraPlateSet);
                $this->addReplacementPlates($extraPlateSet);

                $this->generateRacePlateDataCsv(
                    $riderByName,
                    $extraPlateSet,
                    $outputDir . self::RACE_PLATE_DATA_CSV
                    );

                $this->generateTeamEnvelopeDataCsv(
                    $riderByName,
                    $outputDir . self::TEAM_ENVELOPE_DATA_CSV
                    );

                $this->generateDivisionListPdf(
                    $teamSizeMap,
                    $tmpDir,
                    $outputDir . self::DIVISION_LIST_PDF
                    );
            }

            if (!$categoryMap->isEmpty()) {
                $this->initializeRows($event, $categoryMap);

                $this->generateStagingSheetsPdf(
                    $event,
                    $tmpDir,
                    $outputDir . self::STAGING_SHEETS_PDF
                    );

                $this->generateTeamStagingPdf(
                    $event,
                    $riderByTeamAndRace,
                    $tmpDir,
                    $outputDir . self::TEAM_STAGING_PDF
                    );

                $this->generateStagingSummaryPdf(
                    $event,
                    $tmpDir,
                    $outputDir . self::STAGING_SUMMARY_PDF
                    );

                $this->generateTimingSystemImportCsv(
                    $riderByName,
                    $outputDir . self::TIMING_SYSTEM_IMPORT_CSV
                    );

                $this->generateStagingOrderPdf(
                    $event,
                    $riderByName,
                    $tmpDir . self::RIDERS_BY_NAME_XML,
                    $outputDir . self::STAGING_ORDER_BY_NAME_PDF,
                    "Lastname/Firstname"
                    );

                $this->generateStagingOrderPdf(
                    $event,
                    $riderByCategory,
                    $tmpDir . self::RIDERS_BY_CATEGORY_XML,
                    $outputDir . self::STAGING_ORDER_BY_CATEGORY_PDF,
                    "Category"
                    );

                $this->generateStagingOrderPdf(
                    $event,
                    $riderByBib,
                    $tmpDir . self::RIDERS_BY_CATEGORY_XML,
                    $outputDir . self::STAGING_ORDER_BY_BIB_PDF,
                    "Bib"
                    );
            }
        }

        return $this->getStagingLinks($event);
    }

    /**
     *
     * @return StagingLink[]
     */
    public function getStagingLinks(Event $event): array
    {
        $result = [];
        $wpUploadDir = wp_upload_dir()['basedir'];
        $wpUploadUrl = wp_upload_dir()['baseurl'];
        $outputDir = $this->getOutputDir($event);

        $outputFiles = [
            self::RACE_PLATE_DATA_CSV,
            self::TEAM_ENVELOPE_DATA_CSV,
            self::STAGING_SHEETS_PDF,
            self::TEAM_STAGING_PDF,
            self::STAGING_SUMMARY_PDF,
            self::TIMING_SYSTEM_IMPORT_CSV,
            self::STAGING_ORDER_BY_NAME_PDF,
            self::STAGING_ORDER_BY_CATEGORY_PDF,
            self::STAGING_ORDER_BY_BIB_PDF,
            self::DIVISION_LIST_PDF
        ];

        foreach ($outputFiles as $outputFile) {
            $filePath = $outputDir . $outputFile;

            if (file_exists($filePath)) {
                switch ($outputFile) {
                    case self::RACE_PLATE_DATA_CSV:
                        $linkTitle ='Race Plate CSV';
                        break;

                    case self::TEAM_ENVELOPE_DATA_CSV:
                        $linkTitle = 'Team Envelopes CSV';
                        break;

                    case self::STAGING_SHEETS_PDF:
                        $linkTitle = 'Staging Sheets PDF';
                        break;

                    case self::TEAM_STAGING_PDF:
                        $linkTitle = 'Team Staging PDF';
                        break;

                    case self::STAGING_SUMMARY_PDF:
                        $linkTitle = 'Staging Summary PDF';
                        break;

                    case self::TIMING_SYSTEM_IMPORT_CSV:
                        $linkTitle = 'Timing System Import CSV';
                        break;

                    case self::STAGING_ORDER_BY_NAME_PDF:
                        $linkTitle = 'Riders By Name PDF';
                        break;

                    case self::STAGING_ORDER_BY_CATEGORY_PDF:
                        $linkTitle = 'Riders By Category PDF';
                        break;

                    case self::STAGING_ORDER_BY_BIB_PDF:
                        $linkTitle = 'Riders By Bib PDF';
                        break;

                    case self::DIVISION_LIST_PDF:
                        $linkTitle = "Division List";
                        break;

                    default:
                        break;
                }

                if (isset($linkTitle)) {
                    $result[] = new StagingLink(
                        $linkTitle,
                        $this->replaceWPUploadDirWithUrl($wpUploadDir, $wpUploadUrl, $filePath),
                        mime_content_type($filePath)
                        );
                }
            }
        }

        return $result;
    }

    private function getOutputDir(Event $event): string
    {
        return $this->pluginInfo->getWriteDir() . '/Staging/' . str_replace(' ', '_', $event->getName()) . '/';
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
     * @param RiderBySet[] $riderBySets
     * @param RiderByTeamAndRaceMap $riderByTeamAndRace
     * @param TeamSizeMap $teamSizeMap
     */
    private function initializeRiders(
        RegisteredRiderMap $regRiderMap,
        TimingRiderMap $timingRiderMap,
        CategoryMap $categoryMap,
        array $riderBySets,
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
                $riderByTeamAndRace->add($rider);

                foreach ($riderBySets as $set) {
                    $set->add($rider);
                }
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

            // If there are more than 40 riders on a team, write a second
            // copy of the record
            if ($teamRcd->riderCount > 40) {
                $writer->insertOne($values);
            }
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
                    $writer->writeAttribute('time', $teamRace->getStartTime()->format('g:i A'));

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

    private function generateDivisionListPdf(TeamSizeMap $teamSizeMap, string $xmlDir, string $pdfFile): void
    {
        $xmlFile = $xmlDir . self::DIVISION_LIST_XML;

        $this->generateDivisionListXML($teamSizeMap, $xmlFile);

        $this->generatePDF($xmlFile, self::DIVISION_LIST_XSLT, $pdfFile);
    }

    private function generateDivisionListXML(TeamSizeMap $teamSizeMap, string $xmlFile): void
    {
        $counts = [];

        $writer = new \XMLWriter();
        if ($writer->openUri($xmlFile)) {
            $writer->startDocument('1.0', 'utf-8');
            $writer->startElement('DivisionList');

            $writer->writeElement('Timestamp', $this->runTimestampStr);

            $writer->startElement('ReportHeader');

//             $writer->writeElement('EventName', $event->getName());
//             $writer->writeElement('EventDate', $event->getRaceDateAsString());

            $writer->endElement();      // Report Header

            $writer->startElement('Divisions');

            $hsMsList = [
                'High School' => true,
                'Middle School' => false
            ];

            foreach ($hsMsList as $level => $isHighSchool) {
                $writer->startElement('Level');
                $writer->writeAttribute('name', $level);

                $levelDivisionList = $teamSizeMap->getSchoolDivisionList($isHighSchool);
                ksort($levelDivisionList);

                $divisions = array_keys($levelDivisionList);

                foreach ($divisions as $division) {
                    $minSize = 1000;
                    $maxSize = 0;

                    $writer->startElement('Division');
                    $writer->writeAttribute('name', $division);

                    $teamList = $levelDivisionList[$division];
                    sort($teamList);

                    foreach ($teamList as $team) {
                        $teamSizeEntry = $teamSizeMap->get($team);
                        $teamSize = $isHighSchool ?
                                        $teamSizeEntry->getHighSchoolSize() :
                                        $teamSizeEntry->getMiddleSchoolSize();

                        if ($teamSize > 0) {
                            $minSize = min($minSize, $teamSize);
                            $maxSize = max($maxSize, $teamSize);

                            $writer->startElement('Team');
                            $writer->writeAttribute('name', $team);
                            $writer->writeAttribute('size', strval($teamSize));
                            $writer->endElement();  // Team
                        }
                    }

                    $writer->endElement();  // Division

                    $counts[$level][$division]['min'] = $minSize;
                    $counts[$level][$division]['max'] = $maxSize;
                }

                $writer->endElement();  // Level
            }

            $writer->endElement();      // Divisions

            $writer->startElement('Stats');

            foreach($counts as $levelName => $levelEntries) {
                $writer->startElement('LevelStats');
                $writer->writeAttribute('name', $levelName);

                foreach ($levelEntries as $divisionName => $divisionEntries) {
                    $writer->startElement('DivisionStats');
                    $writer->writeAttribute('name', $divisionName);

                    foreach ($divisionEntries as $minMax => $size) {
                        $writer->writeAttribute($minMax, strval($size));
                    }

                    $writer->endElement();  // DivisionStats
                }

                $writer->endElement();  // LevelStats
            }

            $writer->endElement();      // Stats

            $writer->endElement();      // DivisionList

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
