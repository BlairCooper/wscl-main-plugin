<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceAnalysis;

use Psr\Log\LoggerInterface;
use RCS\PDF\PDFService;
use RCS\PDF\PDFServiceException;
use RCS\WP\PluginInfoInterface;
use WSCL\Main\WsclMainOptionsInterface;
use WSCL\Main\RaceResult\Entity\Event;
use WSCL\Main\RaceResult\Entity\RiderTimingData;


class RiderAnalyzer
{
    private const PERCENT_FORMAT = '%3.1f';
    private const STATE_SUFFIX = ' - State';

    private const VARSITY_GIRLS         = 'Varsity Girls';
    private const VARSITY_BOYS          = 'Varsity Boys';
    private const JV_GIRLS              = 'JV Girls';
    private const JV_BOYS               = 'JV Boys';
    private const HIGH_SCHOOL_2_GIRLS_I = 'High School 2 (Int) Girls';
    private const HIGH_SCHOOL_2_BOYS_I  = 'High School 2 (Int) Boys';
    private const HIGH_SCHOOL_2_GIRLS   = 'High School 2 Girls';
    private const HIGH_SCHOOL_2_BOYS    = 'High School 2 Boys';
    private const INTERMEDIATE_GIRLS    = 'Intermediate Girls';
    private const INTERMEDIATE_BOYS     = 'Intermediate Boys';
    private const HIGH_SCHOOL_1_GIRLS_B = 'High School 1 (Beg) Girls';
    private const HIGH_SCHOOL_1_BOYS_B  = 'High School 1 (Beg) Boys';
    private const HIGH_SCHOOL_1_GIRLS   = 'High School 1 Girls';
    private const HIGH_SCHOOL_1_BOYS    = 'High School 1 Boys';
    private const BEGINNER_GIRLS        = 'Beginner Girls';
    private const BEGINNER_BOYS         = 'Beginner Boys';
    private const ADV_MS_GIRLS          = 'Adv MS Girls';
    private const ADV_MS_BOYS           = 'Adv MS Boys';
    private const GRADE_8_GIRLS         = '8th Grade Girls';
    private const GRADE_8_BOYS          = '8th Grade Boys';
    private const GRADE_7_GIRLS         = '7th Grade Girls';
    private const GRADE_7_BOYS          = '7th Grade Boys';
    private const GRADE_6_GIRLS         = '6th Grade Girls';
    private const GRADE_6_BOYS          = '6th Grade Boys';
    private const GRADE_5_GIRLS         = '5th Grade Girls';
    private const GRADE_5_BOYS          = '5th Grade Boys';
    private const GRADE_4_GIRLS         = '4th Grade Girls';
    private const GRADE_4_BOYS          = '4th Grade Boys';
    private const GRADE_3_GIRLS         = '3rd Grade Girls';
    private const GRADE_3_BOYS          = '3rd Grade Boys';

    private const RIDER_ANALYSIS_XML    = "RiderAnalysis-%d.xml";
    private const RIDER_ANALYSIS_XSLT   = "RiderAnalysis.xslt";
    private const RIDER_ANALYSIS_PDF    = "RiderAnalysis-%d-%s.pdf";
    private const COMMON_XSLT           = "Common.xslt";
    private const LOGO_PNG              = "WSCL_Logo.png";


    /** @var array<string, Category> */
    private array $dataMap;

    private \DateTime $runTimestamp;
    private String $runTimestampStr;


    public function __construct(
        private Event $rrEvent,
        private PluginInfoInterface $pluginInfo,
        private WsclMainOptionsInterface $options,
        private LoggerInterface $logger
        )
    {
        $this->runTimestamp = new \DateTime("now", new \DateTimeZone('America/Los_Angeles'));
        $this->runTimestampStr = $this->runTimestamp->format("Y/m/d H:i:s");

        $varsityGirls   = new Category(self::VARSITY_GIRLS, 0, 110, 0, 100);
        $varsityBoys    = new Category(self::VARSITY_BOYS, 0, 115, 0, 100);

        $jvGirls        = new Category(self::JV_GIRLS, 105, 115, 95, 105);
        $jvBoys         = new Category(self::JV_BOYS, 108, 125, 95, 100);

        $hs2Girls       = new Category(self::HIGH_SCHOOL_2_GIRLS, 102, 115, 95, 100);
        $hs2Boys        = new Category(self::HIGH_SCHOOL_2_BOYS, 110, 130, 100, 110);

        $hs1Girls       = new Category(self::HIGH_SCHOOL_1_GIRLS, 105, 0, 100, 0);
        $hs1Boys        = new Category(self::HIGH_SCHOOL_1_BOYS, 110, 0, 100, 0);

        $advMsGirls     = new Category(self::ADV_MS_GIRLS, 0, 120, 0, 100);
        $advMsBoys      = new Category(self::ADV_MS_BOYS, 0, 120, 0, 100);

        $grade8Girls    = new Category(self::GRADE_8_GIRLS, 99, 0, 100, 0);
        $grade8Boys     = new Category(self::GRADE_8_BOYS, 99, 0, 100, 0);

        $grade7Girls    = new Category(self::GRADE_7_GIRLS, 99, 0, 100, 0);
        $grade7Boys     = new Category(self::GRADE_7_BOYS, 99, 0, 100, 0);

        $grade6Girls    = new Category(self::GRADE_6_GIRLS, 99, 0, 100, 0);
        $grade6Boys     = new Category(self::GRADE_6_BOYS, 99, 0, 100, 0);

        $grade5Girls    = new Category(self::GRADE_5_GIRLS, 99, 0, 100, 0);
        $grade5Boys     = new Category(self::GRADE_5_BOYS, 99, 0, 100, 0);

        $grade4Girls    = new Category(self::GRADE_4_GIRLS, 99, 0, 100, 0);
        $grade4Boys     = new Category(self::GRADE_4_BOYS, 99, 0, 100, 0);

        $grade3Girls    = new Category(self::GRADE_3_GIRLS, 99, 0, 100, 0);
        $grade3Boys     = new Category(self::GRADE_3_BOYS, 99, 0, 100, 0);

        /** Old categories */
        $hs2Girls_i     = new Category(self::HIGH_SCHOOL_2_GIRLS_I, 102, 115, 98, 100);
        $hs2Boys_i      = new Category(self::HIGH_SCHOOL_2_BOYS_I, 110, 130, 100, 110);

        $interGirls     = new Category(self::INTERMEDIATE_GIRLS, 102, 115, 98, 100);
        $interBoys      = new Category(self::INTERMEDIATE_BOYS, 110, 130, 100, 110);

        $hs1Girls_b     = new Category(self::HIGH_SCHOOL_1_GIRLS_B, 105, 0, 100, 0);
        $hs1Boys_b      = new Category(self::HIGH_SCHOOL_1_BOYS_B, 110, 0, 100, 0);

        $begGirls       = new Category(self::BEGINNER_GIRLS, 105, 0, 100, 0);
        $begBoys        = new Category(self::BEGINNER_BOYS, 110, 0, 100, 0);

        $this->dataMap = array(
            self::VARSITY_GIRLS     => $varsityGirls,
            self::VARSITY_BOYS      => $varsityBoys,
            self::JV_GIRLS          => $jvGirls,
            self::JV_BOYS           => $jvBoys,
            self::HIGH_SCHOOL_2_GIRLS => $hs2Girls,
            self::HIGH_SCHOOL_2_BOYS => $hs2Boys,
            self::HIGH_SCHOOL_2_GIRLS_I => $hs2Girls_i,
            self::HIGH_SCHOOL_2_BOYS_I => $hs2Boys_i,
            self::INTERMEDIATE_GIRLS => $interGirls,
            self::INTERMEDIATE_BOYS => $interBoys,
            self::HIGH_SCHOOL_1_GIRLS => $hs1Girls,
            self::HIGH_SCHOOL_1_BOYS  => $hs1Boys,
            self::HIGH_SCHOOL_1_GIRLS_B => $hs1Girls_b,
            self::HIGH_SCHOOL_1_BOYS_B => $hs1Boys_b,
            self::BEGINNER_GIRLS    => $begGirls,
            self::BEGINNER_BOYS     => $begBoys,
            self::ADV_MS_BOYS       => $advMsBoys,
            self::ADV_MS_GIRLS      => $advMsGirls,
            self::GRADE_8_GIRLS     => $grade8Girls,
            self::GRADE_8_BOYS      => $grade8Boys,
            self::GRADE_7_GIRLS     => $grade7Girls,
            self::GRADE_7_BOYS      => $grade7Boys,
            self::GRADE_6_GIRLS     => $grade6Girls,
            self::GRADE_6_BOYS      => $grade6Boys,
            self::GRADE_5_GIRLS     => $grade5Girls,
            self::GRADE_5_BOYS      => $grade5Boys,
            self::GRADE_4_GIRLS     => $grade4Girls,
            self::GRADE_4_BOYS      => $grade4Boys,
            self::GRADE_3_GIRLS     => $grade3Girls,
            self::GRADE_3_BOYS      => $grade3Boys
        );

        $varsityGirls->addCategories(null, $jvGirls);
        $varsityBoys->addCategories(null, $jvBoys);

        if ($rrEvent->isHighSchoolxEra()) {
            $jvGirls->addCategories($varsityGirls, $hs2Girls);
            $jvBoys->addCategories($varsityBoys, $hs2Boys);
        } elseif ($rrEvent->isHighSchoolxTransitionEra()) {
            $jvGirls->addCategories($varsityGirls, $hs2Girls_i);
            $jvBoys->addCategories($varsityBoys, $hs2Boys_i);
        } else {
            $jvGirls->addCategories($varsityGirls, $interGirls);
            $jvBoys->addCategories($varsityBoys, $interBoys);
        }

        $hs2Girls->addCategories($jvGirls, $hs1Girls);
        $hs2Girls_i->addCategories($jvGirls, $hs1Girls_b);
        $interGirls->addCategories($jvGirls, $begGirls);

        $hs2Boys->addCategories($jvBoys, $hs1Boys);
        $hs2Boys_i->addCategories($jvBoys, $hs1Boys_b);
        $interBoys->addCategories($jvBoys, $begBoys);

        $hs1Girls->addCategories($hs2Girls, null);
        $hs1Girls_b->addCategories($hs2Girls_i, null);
        $begGirls->addCategories($interGirls, null);

        $hs1Boys->addCategories($hs2Boys, null);
        $hs1Boys_b->addCategories($hs2Boys_i, null);
        $begBoys->addCategories($interBoys, null);

        $advMsGirls->addCategories($jvGirls, null); // AdvMS can upgrade to JV, not Intermediate
        $advMsBoys->addCategories($jvBoys, null);   // AdvMS can upgrade to JV, not Intermediate

        $grade8Girls->addCategories($advMsGirls, null);
        $grade8Boys->addCategories($advMsBoys, null);

        $grade7Girls->addCategories($advMsGirls, null);
        $grade7Boys->addCategories($advMsBoys, null);

        $grade6Girls->addCategories($advMsGirls, null);
        $grade6Boys->addCategories($advMsBoys, null);

        $grade5Girls->addCategories($advMsGirls, null);
        $grade5Boys->addCategories($advMsBoys, null);

        $grade4Girls->addCategories($advMsGirls, null);
        $grade4Boys->addCategories($advMsBoys, null);

        $grade3Girls->addCategories($advMsGirls, null);
        $grade3Boys->addCategories($advMsBoys, null);

        if ($rrEvent->isFallRace() &&
            !$rrEvent->isHighSchoolxEra() &&
            !$rrEvent->isHighSchoolxTransitionEra()
            )
        {
            // In the fall of 2022 Adv MS would be assigned intermediate
            $advMsGirls->addCategories($jvGirls, $begGirls);
            $advMsBoys->addCategories($jvBoys, $begBoys);

            // In the fall of 2022 8th grade would be assigned beginner so intermediate is the category above
            $grade8Girls->addCategories($interGirls, null);
            $grade8Boys->addCategories($interBoys, null);
        }
    }

    /**
     *
     * @param RiderTimingData[] $timingData
     */
    public function loadData(array $timingData): void
    {
        $this->addRiders($timingData);
        $this->updateCalcs();
    }

    /**
     *
     * @param RiderTimingData[] $riderData
     */
    private function addRiders(array $riderData): void
    {
        $matches = [];
        $pattern = sprintf('/(.*)(?:%s)?$/U', self::STATE_SUFFIX);

        foreach ($riderData as $rider) {
            preg_match($pattern, $rider->category, $matches);

            $riderCategory = $matches[1];

            $category = $this->dataMap[$riderCategory] ?? null;

            if (isset($category)) {
                $category->addRider($rider);
            }
        }
    }

    private function updateCalcs(): void
    {
        // Update the calculations on all of the categories
        foreach ($this->dataMap as $category) {
            $category->performCategoryCalcs();
        }

        // Then update the calculations on all the riders
        foreach ($this->dataMap as $category) {
            $category->performRiderCalcs();
        }
    }

    public function generateReport(): string
    {
        $eventId = $this->rrEvent->getId();

        $outputDir = sprintf('%sRiderAnalysis/', $this->pluginInfo->getWriteDir());
        $tmpDir = $outputDir . 'tmp/';      // temporary directory where we'll put anything we need temporarily.

        // Create the temp directory recursively which include the output directory
        !is_dir($tmpDir) && mkdir($tmpDir, 0755, true);

        $xmlFile = $tmpDir . sprintf(self::RIDER_ANALYSIS_XML, $eventId);
        $pdfFile = $outputDir . sprintf(self::RIDER_ANALYSIS_PDF, $eventId, $this->rrEvent->getName());

        // delete any existing files
        @unlink($xmlFile);
        @unlink($pdfFile);

        $writer = new \XMLWriter();
        if ($writer->openUri($xmlFile)) {
            $writer->startDocument('1.0', 'utf-8');
            $writer->startElement('RiderAnalysis');
            $writer->writeAttribute('EventId', strval($eventId));
            $writer->writeAttribute('EventName', $this->rrEvent->getName());
            $writer->writeAttribute('EventDate', $this->rrEvent->getDate()->format('m/d/Y'));
            $writer->writeAttribute('Timestamp', $this->runTimestampStr);

            foreach ($this->dataMap as $category) {
                $this->generateCategoryAnalysisXML($category, $writer);
            }
            $writer->endElement();      // RiderAnalysis

            $writer->endDocument();
        }

        $this->generatePDF($xmlFile, self::RIDER_ANALYSIS_XSLT, $pdfFile);

        return $pdfFile;
    }

    private function generateCategoryAnalysisXML(Category $category, \XMLWriter $writer): void
    {
        if ($category->hasRiders()) {
            $writer->startElement('Category');

            $writer->startElement('ReportHeader');

            $writer->writeElement('CategoryName', $category->getName());
            $writer->writeElement('AvgLapTime', strval($category->getAvgLapTime()));
            $writer->writeElement('AvgLapAbove', strval($category->getCatAboveAvgLapTime()));
            $writer->writeElement('AvgLapBelow', strval($category->getCatBelowAvgLapTime()));
            $writer->writeElement('NumberOfLaps', strval($category->getNumberOfLaps()));
            $writer->writeElement('CurCatPromotionCutoff', strval($category->getPromotionCutoffCurCat()));
            $writer->writeElement('CurCatRelegationCutoff', strval($category->getRelegationCutoffCurCat()));
            $writer->writeElement('CatAbovePromotionCutoff', strval($category->getPromotionCutoffAboveCat()));
            $writer->writeElement('CatBelowRelegationCutoff', strval($category->getRelegationCutoffBelowCat()));
            $writer->writeElement('CategoryAbove', $category->getCatAboveName());
            $writer->writeElement('CategoryBelow', $category->getCatBelowName());

            $writer->endElement();      // Report Header

            $writer->startElement('Riders');

            foreach ($category->getRiders() as $rider) {
                $writer->startElement('Rider');
                $writer->writeAttribute('Promote', $rider->promotionCandidate ? 'Y' : 'N');
                $writer->writeAttribute('Relegate', $rider->relegationCandidate ? 'Y' : 'N');
                $writer->writeAttribute('MandatoryUpgrade', $rider->mandatoryUpgrade ? 'Y' : 'N');
                $writer->writeAttribute('MandatoryDowngrade', $rider->mandatoryDowngrade ? 'Y' : 'N');

                $writer->writeElement('Rank', sprintf('%d%s', $rider->rank, strpos($rider->category, self::STATE_SUFFIX) ? 's' : ''));
                $writer->writeElement('Bib', strval($rider->bib));
                $writer->writeElement('Name', $rider->fullname);
                $writer->writeElement('Team', $rider->team);
                $writer->writeElement('Grade', strval($rider->grade));
                $writer->writeElement('FastestLap', $rider->fastestLap);
                $writer->writeElement('AverageLap', $rider->averageLap);
                $writer->writeElement('AverageLapSeconds', sprintf('%4.3f', $rider->averageLapSeconds));
                $writer->writeElement('RaceTime', $rider->raceTime);
                $writer->writeElement('GapToFirst', sprintf(self::PERCENT_FORMAT, $rider->gapToFirst));
                $writer->writeElement('PercentOfFirst', sprintf(self::PERCENT_FORMAT, $rider->percentOfFirst));
                $writer->writeElement('PercentOfCatAbove', sprintf(self::PERCENT_FORMAT, $rider->percentofCatAbove));
                $writer->writeElement('PercentOfCatBelow', sprintf(self::PERCENT_FORMAT, $rider->percentofCatBelow));

                $writer->endElement();  // Rider
            }
            $writer->endElement();      // Riders

            $writer->endElement();      // Category
        }
    }

    private function generatePDF(string $xmlFile, string $xsltFile, string $pdfFile): void
    {
        $pluginPath = $this->pluginInfo->getPath();
        $pdfResources = $pluginPath . 'resources/';

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
}
