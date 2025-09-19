<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use RCS\WP\BgProcess\BgTaskInterface;
use WSCL\Main\MailerLite\MailerLiteClient;
use WSCL\Main\MailerLite\Cron\MailerLiteSyncState;
use WSCL\Main\MailerLite\Entity\Group;
use WSCL\Main\MailerLite\Entity\Subscriber;
use WSCL\Main\MailerLite\Entity\SubscriberField;
use WSCL\Main\MailerLite\Types\EmailsMap;
use WSCL\Main\MailerLite\Types\SubscriberMap;

class UpdateMailerLiteTask implements BgTaskInterface
{
    private const GET_SUBSCRIBERS_LIMIT = 500;

    /** Sync state identifier */
    protected string $syncStateId;

    /** UpdateMailerLiteStates */
    protected UpdateMailerLiteStates $state;

    public function __construct(string $syncStateId)
    {
        $this->syncStateId = $syncStateId;
        $this->state = UpdateMailerLiteStates::DETERMINE_MISSING_GROUPS;
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\BgProcess\BgTaskInterface::run()
     */
    public function run(BgProcessInterface $bgProcess, LoggerInterface $logger, array $params) : bool
    {
        /** @var MailerLiteClient */
        $mlClient = $params[MailerLiteClient::class];

        $taskComplete = false;    // assume the task is not going to be complete

        $syncState = MailerLiteSyncState::instance($this->syncStateId);

        switch ($this->state) {
            case UpdateMailerLiteStates::DETERMINE_MISSING_GROUPS:
                // Ensure the flag fields we're using exist or create them
                $this->determineMissingGroups($mlClient, $syncState, $logger);

                $syncState->resetFetchOffset();
                $this->state = UpdateMailerLiteStates::FETCH_GROUP_SUBSCRIBERS;
                break;

            case UpdateMailerLiteStates::FETCH_GROUP_SUBSCRIBERS:
                // Ensure the flag fields we're using exist or create them
                if ($this->fetchGroupSubscribers($mlClient, $syncState)) {
                    $syncState->resetFetchOffset();
                    $this->state = UpdateMailerLiteStates::FETCH_SUBSCRIBERS;
                }
                break;

            case UpdateMailerLiteStates::FETCH_SUBSCRIBERS:
                if ($this->fetchSubscribers($mlClient, $bgProcess, $syncState, $logger)) {
                    $this->state = UpdateMailerLiteStates::ADD_NEW_SUBSCRIBERS;
                }
                break;

            case UpdateMailerLiteStates::ADD_NEW_SUBSCRIBERS:
                $this->addNewSubscribers($bgProcess, $syncState, $logger);
                $this->state = UpdateMailerLiteStates::WAIT_FOR_SUBSCRIBERS_TASKS;

                /**
                 * Push ourselves onto the queue to be part of the new
                 * batch of tasks. Also mark the current iteration of
                 * this task as complete and continue in the new batch.
                 */
                $bgProcess->pushToQueue($this);
                $taskComplete = true;
                break;

            case UpdateMailerLiteStates::WAIT_FOR_SUBSCRIBERS_TASKS:
                if ($syncState->isUpdateSubscribersTasksComplete() &&
                    $syncState->isAddSubscribersTasksComplete())
                {
                    $this->state = UpdateMailerLiteStates::ADD_TO_GROUPS;
                }
                break;

            case UpdateMailerLiteStates::ADD_TO_GROUPS:
                $this->addSubscribersToGroups($bgProcess, $syncState, $logger);
                $this->state = UpdateMailerLiteStates::TASK_COMPLETE;
                break;

            case UpdateMailerLiteStates::TASK_COMPLETE:
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

    private function determineMissingGroups(
        MailerLiteClient $mlClient,
        MailerLiteSyncState $syncState,
        LoggerInterface $logger
        ): void
    {
        $neededGroupNames = $syncState->getEmailsMapRef()->getGroupNames();

        $mlGroups = $mlClient->getGroups();

        if (!is_null($mlGroups)) {
            foreach($neededGroupNames as $groupName) {
                /** @var Group[] */
                $matches = array_filter($mlGroups, fn($mlGroup) => $mlGroup->getName() == $groupName);

                if (empty($matches)) {
                    $newGroup = $mlClient->addGroup(Group::create($groupName));

                    if (is_null($newGroup)) {
                        $logger->error(
                            'Unable to add {group} group to MailerLite',
                            array('group' => $groupName)
                            );
                    } else {
                        $syncState->addMailerLiteGroup($groupName, $newGroup->getId());
                    }
                } else {
                    /** @var Group */
                    $group = array_shift($matches);
                    $syncState->addMailerLiteGroup($groupName, $group->getId());
                }
            }
        }
    }

    /**
     *
     * @return bool
     */
    private function fetchGroupSubscribers(
        MailerLiteClient $mlClient,
        MailerLiteSyncState $syncState
        ): bool
    {
        $doneFetching = false;

        $groupName = $syncState->getCurrentGroup();

        if (is_null($groupName)) {
            $doneFetching = true;
        } else {
            /** @var Subscriber[] */
            $subs = $mlClient->getGroupSubscribers(
                $syncState->getMailerLiteGroupId($groupName),
                $syncState->getFetchOffset(),
                self::GET_SUBSCRIBERS_LIMIT
                );

            if (empty($subs)) {
                $doneFetching = !$syncState->advanceCurrentGroup();
                $syncState->resetFetchOffset(); // Reset the fetch offset in case we changed groups
            } else {
                foreach ($subs as $sub) {
                    $emailsMap = &$syncState->getEmailsMapRef();

                    $emailsMap->remove($groupName, $sub->getEmail());
                }

                $syncState->advanceFetchOffset(self::GET_SUBSCRIBERS_LIMIT);
            }
        }

        return $doneFetching;
    }

    /**
     *
     * @return bool
     */
    private function fetchSubscribers(
        MailerLiteClient $mlClient,
        BgProcessInterface $bgProcess,
        MailerLiteSyncState $syncState,
        LoggerInterface $logger
        ): bool
    {
        $doneFetching = false;

        /** @var Subscriber[] */
        $subs = $mlClient->getSubscribers($syncState->getFetchOffset(), self::GET_SUBSCRIBERS_LIMIT);

        if (empty($subs)) {
            $doneFetching = true;
        } else {
            $this->patchApostrophes($subs, "last_name");
            $this->patchApostrophes($subs, "city");

            $this->generateUpdateTasks($subs, $syncState->getCcnSubscribers(), $bgProcess, $syncState, $logger);
            $syncState->advanceFetchOffset(self::GET_SUBSCRIBERS_LIMIT);
        }

        return $doneFetching;
    }

    /**
     *
     * @param Subscriber[] $mlSubs
     * @param SubscriberMap $ccnSubscriberMap
     * @param BgProcessInterface $bgProcess
     * @param MailerLiteSyncState $syncState
     */
    private function generateUpdateTasks(
        array $mlSubs,
        SubscriberMap $ccnSubscriberMap,
        BgProcessInterface $bgProcess,
        MailerLiteSyncState $syncState,
        LoggerInterface $logger
        ): void
    {
        /** @var Subscriber[] */
        $subsToUpdate = array();

        foreach ($mlSubs as $mlSubscriber) {
            /** @var Subscriber|NULL */
            $ccnSubscriber = $ccnSubscriberMap->remove(strtolower($mlSubscriber->getEmail()));

            if (!is_null($ccnSubscriber)) {
                /** @var Subscriber|NULL */
                $mergeSub = $this->mergeSubscriber($mlSubscriber, $ccnSubscriber);

                if (!is_null($mergeSub)) {
                    $ccnSubscriber->getField(Subscriber::COACH);
                    $subsToUpdate[] = $mergeSub;
                }
            }
            // else subscriber in ML, not in CCN: ignore
        }

        // Update existing subscribers with changes
        foreach ($subsToUpdate as $sub) {
            $bgProcess->pushToQueue(new UpdateSubscriberTask($sub, $this->syncStateId));
        }

        $syncState->addUpdateSubIds(
            array_map(
                fn($sub): int => $sub->getId(),
                $subsToUpdate
                )
            );

        $logger->info('Updating {cnt} subscribers in MailerLite', array('cnt' => count($subsToUpdate)));
    }

    private function addNewSubscribers(BgProcessInterface $bgProcess, MailerLiteSyncState $syncState, LoggerInterface $logger): void
    {
        /** @var SubscriberMap */
        $ccnSubscriberMap = $syncState->getCcnSubscribers();

        // At this point the only things left in the map should be
        // subscribers that are in CCN but not MailerLite
        foreach ($ccnSubscriberMap->values() as $sub) {
            $bgProcess->pushToQueue(new AddSubscriberTask($sub, $this->syncStateId));
        }

        $syncState->addNewSubsHashes(
            array_map(
                fn($sub): string => $sub->hashcode(),
                $ccnSubscriberMap->values()
            )
        );

        $logger->info('Adding {cnt} subscribers to MailerLite', array('cnt' => count($ccnSubscriberMap)));
    }

    private function addSubscribersToGroups(
        BgProcessInterface $bgProcess,
        MailerLiteSyncState $syncState,
        LoggerInterface $logger
        ): void
    {
        /** @var EmailsMap */
        $emailsMap = &$syncState->getEmailsMapRef();

        foreach ($emailsMap->getGroupNames() as $groupName) {
            $groupId = $syncState->getMailerLiteGroupId($groupName);
            $emailList = array_unique($emailsMap->get($groupName));

            if (!empty($emailList)) {
                $bgProcess->pushToQueue(new AddSubscribersToGroupTask($groupId, $emailList));

                $logger->info(
                    'Adding {cnt} subscribers to MailerLite {group} group',
                    [
                        'cnt' => count($emailList),
                        'group' => $groupName
                    ]
                    );
            }
        }
    }

    /**
     * Remove the backslashes from field values with apostrophes like O'Neil
     * or Coeur d'Alene.
     *
     * MailerLite returns field values with apostrophes like O'Neil as
     * O\\'Neil. CCN doesn't resulting in a mismatch and an attempt to update
     * a field that doesn't need updating.
     *
     * @param Subscriber[] $subs An array of Subscribers.
     * @param string $fieldName The name of the field to patch.
     */
    private function patchApostrophes(array $subs, string $fieldName): void
    {
        foreach ($subs as $sub) {
            /** @var SubscriberField|NULL */
            $field = $sub->getField($fieldName);

            if (!is_null($field)) {
                $value = $field->getValue();

                $count = 0;
                $value = str_replace('\\', '', $value, $count);

                if ($count > 0) {
                    $field->setValue($value);
                }
            }
        }
    }

    /**
     * @return Subscriber|NULL The merged subscriber.
     */
    private function mergeSubscriber(Subscriber $mlSubscriber, Subscriber $ccnSubscriber): ?Subscriber
    {
        /** @var Subscriber */
        $mergedSubscriber = new Subscriber();

        // Check there is no name in MailerLite but there is one in CCN
        if (empty($mlSubscriber->getName()) && !empty($ccnSubscriber->getName())) {
            $mergedSubscriber->setName($ccnSubscriber->getName());
        }

        // Look for any field changes
        foreach ($ccnSubscriber->getFields() as $field) {
            /** @var SubscriberField|NULL */
            $orgField = $mlSubscriber->getField($field->getKey());

            if (is_null($orgField) || // new field doesn't exist in the original
                (!is_null($field->getValue()) &&
                 0 !== strcasecmp($orgField->getValue(), $field->getValue()) // field has different value
                )
            ) {
                $mergedSubscriber->addField($field);
            }
        }

        // Check if nothing changed
        if (empty($mergedSubscriber->getFields())) {
            $mergedSubscriber = null;    // don't return anything
        } else {
            $mergedSubscriber->setId($mlSubscriber->getId());
            $mergedSubscriber->setEmail($mlSubscriber->getEmail());
            $mergedSubscriber->setType($mlSubscriber->getType());

            if (is_null($mergedSubscriber->getName()) || empty($mergedSubscriber->getName())) {
                $mergedSubscriber->setName($mlSubscriber->getName());
            }
        }

        return $mergedSubscriber;
    }
}
