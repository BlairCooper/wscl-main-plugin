<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

use JsonMapper\JsonMapperBuilder;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use JsonMapper\Handler\PropertyMapper;
use League\Csv\Reader;
use League\Csv\Statement;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\CcnBikes\Csv\AthleteMembershipRcd;
use WSCL\Main\CcnBikes\Csv\CoachMembershipRcd;
use WSCL\Main\CcnBikes\Csv\MembershipRcd;
use WSCL\Main\CcnBikes\Entity\MembershipOrg;
use WSCL\Main\CcnBikes\Entity\MembershipOrgConfig;
use WSCL\Main\CcnBikes\Entity\MembershipOrgReportData;
use WSCL\Main\CcnBikes\Entity\ReportStartResponse;
use WSCL\Main\CcnBikes\Entity\ReportType;
use WSCL\Main\CcnBikes\Enums\ReportStateEnum;
use WSCL\Main\CcnBikes\Json\CcnFactoryRegistry;
use WSCL\Main\MailerLite\Cron\MailerLiteSyncState;
use WSCL\Main\MailerLite\Entity\CcnSubscriber;
use WSCL\Main\MailerLite\Entity\Subscriber;
use WSCL\Main\MailerLite\Entity\SubscriberField;
use WSCL\Main\MailerLite\Enums\FieldType;
use WSCL\Main\CcnBikes\Enums\MembershipReportStatus;
use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use RCS\WP\BgProcess\BgTaskInterface;
use RCS\Traits\SerializeAsArrayTrait;

class FetchCcnSubscribersTask implements BgTaskInterface
{
    use SerializeAsArrayTrait;

    private const MEMBERSHIPS_ALL_REPORT = 'Memberships - All';
    private const REPORT_TIMEOUT_PERIOD = 5 * MINUTE_IN_SECONDS * 1.0;    // In seconds
    private const TASK_TIMEOUT_PERIOD = 60 * MINUTE_IN_SECONDS * 1.0;    // In seconds

    protected string $syncId;
    protected int $orgId;
    protected bool $isAthleteOrg;
    protected float $reportWaitTime;
    protected float $reportStartTime;
    protected float $taskStartTime;

    protected string $groupPrefix;

    /** @var string Will contain the report UUID, URL or filename depending on where the process is */
    protected string $transientReportValue;

    protected FetchSubsStates $state;

    public function __construct(MembershipOrg $org, string $syncId)
    {
        $this->state = FetchSubsStates::GET_MEMBERSHIP_CONFIG;

        $this->syncId = $syncId;
        $this->orgId = $org->getId();
        $this->isAthleteOrg = $org->isAthleteOrg();
        $this->reportWaitTime = 0;
        $this->taskStartTime = microtime(true);
    }

    public function isComplete(): bool
    {
        return FetchSubsStates::SUBTASK_COMPLETE == $this->state;
    }

    public function isFailed(): bool
    {
        return FetchSubsStates::SUBTASK_FAILED == $this->state;
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\BgProcess\BgTaskInterface::run()
     */
    public function run(BgProcessInterface $bgProcess, LoggerInterface $logger, array $params) : bool
    {
        /** @var CcnClient */
        $ccnClient = $params[CcnClient::class];

        $taskComplete = false;    // assume the task is not going to be complete

        $syncState = MailerLiteSyncState::instance($this->syncId);

        if (microtime(true) > $this->taskStartTime + self::TASK_TIMEOUT_PERIOD) {
            // If we've taken too long, bail
            $this->state = FetchSubsStates::SUBTASK_FAILED;
        }

        switch ($this->state) {
            case FetchSubsStates::GET_MEMBERSHIP_CONFIG:
                $logger->info('Fetching membership config for {org}', array('org' => $this->orgId));
                $this->getMembershipConfig($ccnClient, $logger);
                break;

            case FetchSubsStates::GET_MEMBERSHIP_REPORT_DATA:
                $logger->info('Fetching report data for {org}', array('org' => $this->orgId));
                $this->getReportData($ccnClient);
                break;

            case FetchSubsStates::WAIT_FOR_REPORT:
                $timestamp = microtime(true);

                if ($timestamp > $this->reportWaitTime) {
                    $logger->info('Checking report status for {org}', array('org' => $this->orgId));
                    $this->checkReportStatus($ccnClient, $logger);
                    $this->reportWaitTime = $timestamp + 5;   // In seconds
                }
                break;

            case FetchSubsStates::DOWNLOAD_REPORT:
                $logger->info('Downloading report for {org}', array('org' => $this->orgId));
                $this->downloadReportFile($ccnClient, $logger);
                break;

            case FetchSubsStates::PROCESS_REPORT:
                $logger->info('Processing report for {org}', array('org' => $this->orgId));
                $this->processReport($syncState, $logger);
                break;

            case FetchSubsStates::SUBTASK_COMPLETE:
            case FetchSubsStates::SUBTASK_FAILED:
                $logger->info('Task complete for {org}', array('org' => $this->orgId));
                $syncState->removeOrgId($this->orgId);
                $taskComplete = true;
                break;

            default:    // should never happen but if it does just say the task is complete.
                $logger->warning(
                    'Unexpected state in task for {org}, marking task complete',
                    array('org' => $this->orgId)
                    );
                $taskComplete = true;
                break;
        }

        $syncState->save();

        return $taskComplete;
    }

    private function getMembershipConfig(CcnClient $ccnClient, LoggerInterface $logger): void
    {
        /** @var MembershipOrgConfig|NULL */
        $config = $ccnClient->getMembershipOrganizationConfig($this->orgId);

        if (!is_null($config)) {
            if ($config->isInWindow()) {
                $this->groupPrefix = sprintf('%s %s ', $config->getSeason()->value, $config->getYear());    // 'Fall 2025 '

                $this->state = FetchSubsStates::GET_MEMBERSHIP_REPORT_DATA;
            } else {
                $this->state = FetchSubsStates::SUBTASK_COMPLETE;
            }
        } else {
            $logger->error('Unable to get CCN Membership Config');
        }
    }

    private function getReportData(CcnClient $ccnClient): void
    {
        /** @var MembershipOrgReportData|NULL */
        $reportData = $ccnClient->getMembershipReportData($this->orgId);

        if (!is_null($reportData)) {
            // Find the membership report
            /** @var ReportType|NULL */
            $rptType = $reportData->findReportType(self::MEMBERSHIPS_ALL_REPORT);

            if (!is_null($rptType)) {
                // Start the report run
                /** @var ReportStartResponse */
                $startResp = $ccnClient->startReport($rptType->updateLink);

                $this->reportStartTime = microtime(true);
                $this->transientReportValue = $startResp->taskUuid;

                $this->state = FetchSubsStates::WAIT_FOR_REPORT;
            } else {
                $this->state = FetchSubsStates::SUBTASK_FAILED;
            }
        } else {
            $this->state = FetchSubsStates::SUBTASK_FAILED;
        }
    }

    private function checkReportStatus(CcnClient $ccnClient, LoggerInterface $logger): void
    {
        /** @var ReportStateEnum */
        $rptState = $ccnClient->getReportState($this->transientReportValue);

        if (ReportStateEnum::SUCCESS == $rptState) {
            /** @var MembershipOrgReportData|NULL Get the (updated) report data */
            $reportData = $ccnClient->getMembershipReportData($this->orgId);

            if (!is_null($reportData)) {
                // Find the membership report
                $url = $reportData->getReportDownloadUrl(self::MEMBERSHIPS_ALL_REPORT);

                if (!is_null($url)) {
                    $this->transientReportValue = $url;
                    $this->state = FetchSubsStates::DOWNLOAD_REPORT;
                } else {
                    $logger->error(
                        'ReportData missing URL for org {orgId}: {rpt}',
                        [
                            'orgId' => $this->orgId,
                            'rpt' => var_export($reportData, true)
                        ]
                        );
                    $this->state = FetchSubsStates::SUBTASK_FAILED;
                }
            } else {
                $logger->error('Failed to download report for org {orgId}', array('orgId' => $this->orgId));
                $this->state = FetchSubsStates::SUBTASK_FAILED;
            }
        } else {
            if (0 != $this->reportWaitTime &&
                $this->reportWaitTime > $this->reportStartTime + self::REPORT_TIMEOUT_PERIOD
                ) {
                $this->state = FetchSubsStates::SUBTASK_FAILED;
            }
        }
    }

    private function downloadReportFile(CcnClient $ccnClient, LoggerInterface $logger): void
    {
        // Download the file
        $file = $ccnClient->downloadFile($this->transientReportValue);

        if (!is_null($file)) {
            $this->transientReportValue = $file;
            $this->state = FetchSubsStates::PROCESS_REPORT;
        } else {
            $logger->error('Failed to download report for {orgId}', array('orgId' => $this->orgId));
            $this->state = FetchSubsStates::SUBTASK_FAILED;
        }
    }

    private function processReport(MailerLiteSyncState $syncState, LoggerInterface $logger): void
    {
        $subList = array();

        if ($this->isAthleteOrg) {
            $subList = $this->processAthleteReport($logger);
        } else {
            $subList = $this->processCoachReport();
        }

        $syncState->addCcnSubscribers($subList);

        unlink($this->transientReportValue);

        $this->transientReportValue = '';

        $this->state = FetchSubsStates::SUBTASK_COMPLETE;
    }

    /**
     *
     * @return CcnSubscriber[]
     */
    private function processAthleteReport(LoggerInterface $logger): array
    {
        /** @var CcnSubscriber[] */
        $subList = array();

        /** @var AthleteMembershipRcd[] */
        $athletes = $this->loadMembershipFile($this->transientReportValue, AthleteMembershipRcd::class);

        $studentCnt = 0;
        $parentCnt = 1;

        foreach ($athletes as $athlete) {
            if (MembershipReportStatus::ISSUED == $athlete->getStatus()) {
                if (!empty($athlete->getEmail())) {
                    /** @var CcnSubscriber */
                    $sub = $this->memberToSubscriber($athlete, "Students");
                    $sub->addField(new SubscriberField(Subscriber::STUDENT, FieldType::TEXT, "1"));
                    $studentCnt++;

                    array_push($subList, $sub);
                }

                if (!empty($athlete->getParent1Email())) {
                    /** @var CcnSubscriber */
                    $sub = CcnSubscriber::create($athlete->getParent1Email(), $athlete->getParent1FirstName());
                    $sub->setGroupName($this->groupPrefix . "Parents");
                    $parentCnt++;

                    $sub->addField(
                        new SubscriberField(Subscriber::LAST_NAME, FieldType::TEXT, $athlete->getParent1LastName())
                        );
                    $sub->addField(
                        new SubscriberField(Subscriber::PHONE, FieldType::TEXT, $athlete->getParent1Phone())
                        );
                    $sub->addField(new SubscriberField(Subscriber::ADDRESS, FieldType::TEXT, $athlete->getAddress()));
                    $sub->addField(new SubscriberField(Subscriber::CITY, FieldType::TEXT, $athlete->getCity()));
                    $sub->addField(new SubscriberField(Subscriber::STATE, FieldType::TEXT, $athlete->getState()));
                    $sub->addField(new SubscriberField(Subscriber::ZIP, FieldType::TEXT, $athlete->getZipCode()));
                    $sub->addField(new SubscriberField(Subscriber::PARENT, FieldType::TEXT, "1"));

                    array_push($subList, $sub);
                }

                if (!empty($athlete->getParent2Email())) {
                    /** @var CcnSubscriber */
                    $sub = CcnSubscriber::create($athlete->getParent2Email(), $athlete->getParent2FirstName());
                    $sub->setGroupName($this->groupPrefix . "Parents");
                    $parentCnt++;

                    $sub->addField(
                        new SubscriberField(Subscriber::LAST_NAME, FieldType::TEXT, $athlete->getParent2LastName())
                        );
                    $sub->addField(
                        new SubscriberField(Subscriber::PHONE, FieldType::TEXT, $athlete->getParent2Phone())
                        );
                    $sub->addField(new SubscriberField(Subscriber::ADDRESS, FieldType::TEXT, $athlete->getAddress()));
                    $sub->addField(new SubscriberField(Subscriber::CITY, FieldType::TEXT, $athlete->getCity()));
                    $sub->addField(new SubscriberField(Subscriber::STATE, FieldType::TEXT, $athlete->getState()));
                    $sub->addField(new SubscriberField(Subscriber::ZIP, FieldType::TEXT, $athlete->getZipCode()));
                    $sub->addField(new SubscriberField(Subscriber::PARENT, FieldType::TEXT, "1"));

                    array_push($subList, $sub);
                }
            }
        }

        $logger->info('Identified {pc} parents and {sc} students from report for {orgId}', array('orgId' => $this->orgId, 'pc' => $parentCnt, 'sc' => $studentCnt));

        return $subList;
    }

    /**
     *
     * @return CcnSubscriber[]
     */
    private function processCoachReport(): array
    {
        /** @var CcnSubscriber[] */
        $subList = array();

        /** @var CoachMembershipRcd[] */
        $coaches = $this->loadMembershipFile($this->transientReportValue, CoachMembershipRcd::class);

        foreach ($coaches as $coach) {
            if (MembershipReportStatus::INCOMPLETE != $coach->getStatus() &&
                !empty($coach->getEmail())
                )
            {
                /** @var CcnSubscriber */
                $sub = $this->memberToSubscriber($coach, "Coaches");
                $sub->addField(new SubscriberField(Subscriber::COACH, FieldType::TEXT, "1"));

                array_push($subList, $sub);
            }
        }

        return $subList;
    }


    /**
     *
     * @param string $filename
     * @param string $clazz
     *
     * @return array<mixed>
     */
    private function loadMembershipFile(string $filename, string $clazz): array
    {
        $members = array();

        // Create our own builder so we can include a PropertyMapper with additional class factories
        $builder = JsonMapperBuilder::new()
        ->withPropertyMapper(new PropertyMapper(CcnFactoryRegistry::withPhpClassesAdded(true)));

        /** @var JsonMapperInterface The JsonMapper to use in mapping JSON to objects */
        $mapper = (new JsonMapperFactory($builder))->bestFit();

        $reader = Reader::createFromPath($filename);
        $reader->skipInputBOM();
        $reader->setHeaderOffset(0);

        $header = $reader->getHeader(); //returns the CSV header record
        if (!empty($header)) {
            $header = $this->remapHeaders($header, $clazz::getColumnPropertyMap());

            $resultSet = Statement::create()
                ->where(array('WSCL\Main\CcnBikes\Csv\RecordFilter', 'leagueCsvFilter'))
                ->process($reader);

            $records = $resultSet->getRecords($header);

            foreach ($records as $rcd) {
                $json = json_encode($rcd);

                $member = $mapper->mapObjectFromString($json, new $clazz());
                array_push($members, $member);
            }
        }

        return $members;
    }

    /**
     * Renames values in the header array with values provided in the mapping
     * array.
     *
     * @param string[] $header The array of headers
     * @param array<string, string> $mapping A mapping of old headers to new headers.
     *
     * @return string[] The updated header array.
     */
    private function remapHeaders(array $header, array $mapping): array
    {
        $header = $this->fixDuplicates($header);

        return array_map(
            fn ($field) => $mapping[$field] ?? $field,
            $header
            );
    }

    /**
     *
     * @param string[] $inArray
     *
     * @return string[]
     */
    private function fixDuplicates(array $inArray): array
    {
        $outArray = array();

        foreach ($inArray as $entry) {
            if (in_array($entry, $outArray)) {
                array_push($outArray, sprintf('%s - %s', $entry, strval(rand(1000, 10000))));
            } else {
                array_push($outArray, $entry);
            }
        }

        return $outArray;
    }

    private function memberToSubscriber(MembershipRcd $member, string $groupSuffix): CcnSubscriber
    {
        /** @var CcnSubscriber */
        $sub = CcnSubscriber::create($member->getEmail(), $member->getFirstName());
        $sub->setGroupName($this->groupPrefix . $groupSuffix);

        $sub->addField(new SubscriberField(Subscriber::LAST_NAME, FieldType::TEXT, $member->getLastName()));
        $sub->addField(new SubscriberField(Subscriber::ADDRESS, FieldType::TEXT, $member->getAddress()));
        $sub->addField(new SubscriberField(Subscriber::CITY, FieldType::TEXT, $member->getCity()));
        $sub->addField(new SubscriberField(Subscriber::STATE, FieldType::TEXT, $member->getState()));
        $sub->addField(new SubscriberField(Subscriber::ZIP, FieldType::TEXT, $member->getZipCode()));
        $sub->addField(new SubscriberField(Subscriber::PHONE, FieldType::TEXT, $member->getPhone()));

        return $sub;
    }

    /**
     * {@inheritDoc}
     * @see \RCS\Traits\SerializeAsArrayTrait::postUnserialize()
     */
    protected function postUnserialize(): void
    {
        if ($this->reportWaitTime > 0 && !isset($this->reportStartTime)) {
            $this->reportStartTime = microtime(true);
        }
    }
}
