<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\CcnBikes\Entity\MembershipOrg;
use WSCL\Main\MailerLite\Cron\MailerLiteSyncState;
use RCS\WP\BgProcess\BgTaskInterface;

class SyncSubscribersTask implements BgTaskInterface
{
    /** SyncSubscribersStates */
    protected SyncSubscribersStates $state;

    /** Sync state identifier */
    protected string $syncStateId;

    public function __construct()
    {
        $this->state = SyncSubscribersStates::FETCH_MEMBERSHIP_ORGS;
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

        if (!isset($this->syncStateId)) {
            $this->syncStateId = MailerLiteSyncState::generateId();
        }

        $syncState = MailerLiteSyncState::instance($this->syncStateId);

        switch ($this->state) {
            // Fetch the membership organizations and store them in a transient
            case SyncSubscribersStates::FETCH_MEMBERSHIP_ORGS:
            {
                /** @var MembershipOrg[] */
                $membershipOrgs = $ccnClient->getMembershipOrgs();

                $orgIds = array();

                // Initialize SyncState

                if (0 != count($membershipOrgs)) {
                    foreach ($membershipOrgs as $org) {
                        if ($org->isActive()) {
                            $task = new FetchCcnSubscribersTask($org, $this->syncStateId);
                            array_push($orgIds, $org->getId());
                            $bgProcess->pushToQueue($task);
                        }
                    }

                    $logger->info(
                        'Checking subscribers on {cnt} organizations',
                        array ('cnt' => count($orgIds))
                        );

                    $syncState->addOrgIds($orgIds);

                    $this->state = SyncSubscribersStates::WAIT_FOR_FETCH_SUBSCRIBERS_TASKS;

                    /**
                     * Push ourselves onto the queue to be part of the new
                     * batch of tasks. Also mark the current iteration of
                     * this task as complete and continue in the new batch.
                     */
                    $bgProcess->pushToQueue($this);
                } else {
                    $logger->error('Failed to retrieve any membership organizations');
                }
                // Mark this iteration of the task complete
                $taskComplete = true;
                break;
            }

            case SyncSubscribersStates::WAIT_FOR_FETCH_SUBSCRIBERS_TASKS:
                if ($syncState->isFetchCcnSubscribersTasksComplete()) {
                    $this->state = SyncSubscribersStates::FETCH_MEMBERSHIP_SUBSCRIBERS;
                }
                break;

            case SyncSubscribersStates::FETCH_MEMBERSHIP_SUBSCRIBERS:
                $logger->info('Updating subscribers in MailerLite');
                $bgProcess->pushToQueue(new UpdateMailerLiteTask($this->syncStateId));
                $taskComplete = true;
                break;

            default:    // should never happen but if it does just say the task is complete.
                $logger->warning('Unexpected state in task, marking task complete');
                $taskComplete = true;
                break;
        }

        $syncState->save();

        return $taskComplete;
    }
}
