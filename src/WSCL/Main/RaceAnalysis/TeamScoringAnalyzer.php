<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceAnalysis;

use Psr\Log\LoggerInterface;
use RCS\PDF\PDFService;
use RCS\PDF\PDFServiceException;
use RCS\WP\PluginInfoInterface;
use WSCL\Main\WsclMainOptionsInterface;
use WSCL\Main\RaceResult\Entity\Event;
use WSCL\Main\RaceResult\Entity\TeamScoringData;


class TeamScoringAnalyzer
{
    private const TEAM_SCORING_ANALYSIS_XML     = "TeamScoringAnalysis-%d.xml";
    private const TEAM_SCORING_ANALYSIS_XSLT    = "TeamScoringAnalysis.xslt";
    private const TEAM_SCORING_ANALYSIS_PDF     = "TeamScoringAnalysis-%d.pdf";
    private const COMMON_XSLT                   = "Common.xslt";
    private const LOGO_PNG                      = "WSCL_Logo.png";

    /** @var array<string, Division> */
    private array $dataMap = array();

    private \DateTime $runTimestamp;
    private String $runTimestampStr;

    /** @var array<int, int> */
    private array $categoryCnt;

    public function __construct(
        private Event $rrEvent,
        private PluginInfoInterface $pluginInfo,
        private WsclMainOptionsInterface $options,
        private LoggerInterface $logger
        )
    {
        $this->runTimestamp = new \DateTime("now", new \DateTimeZone('America/Los_Angeles'));
        $this->runTimestampStr = $this->runTimestamp->format("Y/m/d H:i:s");

        $this->categoryCnt = array();
    }

    /**
     *
     * @param TeamScoringData[] $timingData
     */
    public function loadData(array $timingData): void
    {
        $this->addRiders($timingData);
    }

    /**
     *
     * @param TeamScoringData[] $riderData
     */
    private function addRiders(array $riderData): void
    {
        foreach ($riderData as $rider) {
            $division = $this->dataMap[$rider->division] ?? null;

            $catgoryNdx = substr($rider->category, 0, 1);

            if ($rider->started) {
                $this->categoryCnt[$catgoryNdx] = ($this->categoryCnt[$catgoryNdx] ?? 0) + 1;
            }

            if (!isset($division)) {
                $division = new Division($rider->division);
                $this->dataMap[$division->getName()] = $division;
            }

            $division->addRider($rider);
        }
    }

    public function generateReport(): string
    {
        $eventId = $this->rrEvent->getId();

        $outputDir = sprintf('%s/RaceAnalysis/', $this->pluginInfo->getWriteDir());
        $tmpDir = $outputDir . 'tmp/';      // temporary directory where we'll put anything we need temporarily.

        // Create the temp directory recursively which include the output directory
        !is_dir($tmpDir) && mkdir($tmpDir, 0755, true);

        $xmlFile = $tmpDir . sprintf(self::TEAM_SCORING_ANALYSIS_XML, $eventId);
        $pdfFile = $outputDir . sprintf(self::TEAM_SCORING_ANALYSIS_PDF, $eventId);

        // delete any existing files
        @unlink($xmlFile);
        @unlink($pdfFile);

        $writer = new \XMLWriter();
        if ($writer->openUri($xmlFile)) {
            $writer->startDocument('1.0', 'utf-8');
            $writer->startElement('TeamScoringAnalysis');
            $writer->writeAttribute('EventId', strval($eventId));
            $writer->writeAttribute('EventName', $this->rrEvent->getName());
            $writer->writeAttribute('EventDate', $this->rrEvent->getDate()->format('m/d/Y'));
            $writer->writeAttribute('Timestamp', $this->runTimestampStr);

            $writer->startElement('Divisions');

            $divisions = array_filter(
                $this->getDivisions(),
                fn($division) => self::isHighSchool($division->getName())
                );

            $writer->startElement('HighSchool');
            $writer->writeElement('ReportHeader', '');  // Included to trigger writing header
            $this->writeDivisions($divisions, $writer);
            $writer->endElement();      // HighSchool

            $divisions = array_filter(
                $this->getDivisions(),
                fn($division) => !self::isHighSchool($division->getName())
                );

            $writer->startElement('MiddleSchool');
            $writer->writeElement('ReportHeader', '');  // Included to trigger writing header
            $this->writeDivisions($divisions, $writer);
            $writer->endElement();      // MiddleSchool

            $writer->endElement();      // Divisions

            $writer->endElement();      // TeamScoringAnalysis

            $writer->endDocument();
        }

        $this->generatePDF($xmlFile, self::TEAM_SCORING_ANALYSIS_XSLT, $pdfFile);

        return $pdfFile;
    }

    /**
     *
     * @param array<string, Division> $divisions
     * @param \XMLWriter $writer
     */
    private function writeDivisions(array $divisions, \XMLWriter $writer): void
    {
        foreach ($divisions as $division) {
            $this->generateDivisionAnalysisXML($division, $writer);
        }
    }

    private function generateDivisionAnalysisXML(Division $division, \XMLWriter $writer): void
    {
        $isD2 = preg_match('/.*D2$/', $division->getName());

        $writer->startElement('Division');

        $writer->writeAttribute('Name', $division->getName());
        $writer->writeAttribute('HighSchool', self::isHighSchool($division->getName()) ? 'Y' : 'N');
        $writer->writeAttribute('TeamCount', strval($division->getTeamCount()));

        $this->writeCategoryCounts($division->getName(), array($this, 'getCategoryCount'), $writer);

        $writer->startElement('Teams');

        foreach ($division->getTeams() as $team) {
            $writer->startElement('Team');
            $writer->writeAttribute('Name', $team->getName());

            if ($isD2) {
                if ($team->getFinishedCount() < 3) {
                    $writer->writeAttribute('Excluded', 'Y');
                }
            } else {
                if ($team->getFinishedCount() < 5) {
                    $writer->writeAttribute('Excluded', 'Y');
                }
            }

            $this->writeCategoryCounts($division->getName(), array($team, 'getCategoryCount'), $writer);

            $writer->writeElement('Riders', strval($team->getRiderCount()));
            $writer->writeElement('Started', strval($team->getStartedCount()));
            $writer->writeElement('Finished', strval($team->getFinishedCount()));

            $writer->writeElement('Female', strval($team->getFemaleCount()));
            $writer->writeElement('Male', strval($team->getMaleCount()));

            $writer->writeElement('Rank', strval($isD2 ? $team->getTop3Rank() : $team->getTop5Rank()));
            $writer->writeElement('Points', strval($isD2 ? $team->getTop3Points() : $team->getTop5Points()));

            $writer->endElement();  // Team
        }
        $writer->endElement();      // Teams

        $writer->endElement();      // Division
    }

    private function writeCategoryCounts(string $divisionName, callable $catCountFunction, \XMLWriter $writer): void
    {
        if (self::isHighSchool($divisionName)) {
            $catMap = array(
                '1' => 'B',
                '2' => 'I',
                '3' => 'J',
                '4' => 'V'
            );
        } else {
            $catMap = array(
                '1' => '6',
                '2' => '7',
                '3' => '8',
                '4' => 'A'
            );
        }

        foreach ($catMap as $ndx => $catNdx) {
            $writer->writeAttribute('Category_' . $ndx, strval($catCountFunction($catNdx)));
        }
    }

    public static function isHighSchool(string $division): bool
    {
        return preg_match("/^High School.*$/", $division);
    }

    private function generatePDF(string $xmlFile, string $xsltFile, string $pdfFile): void
    {
        // TODO: Confirm pluginfo->path is sufficient
        $pluginPath = trailingslashit(WP_PLUGIN_DIR) . $this->pluginInfo->getPath();
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

    private function getCategoryCount(string $ndx): int
    {
        return $this->categoryCnt[$ndx] ?? 0;
    }

    /**
     *
     * @return array<string, Division>
     */
    private function getDivisions(): array
    {
        ksort($this->dataMap);

        return $this->dataMap;
    }
}
