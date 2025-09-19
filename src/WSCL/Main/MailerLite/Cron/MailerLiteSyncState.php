<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Cron;

use WSCL\Main\MailerLite\Entity\CcnSubscriber;
use WSCL\Main\MailerLite\Types\SubscriberMap;
use WSCL\Main\MailerLite\Types\EmailsMap;
use WSCL\Main\MailerLite\Types\GroupIdMap;

/**
 * Class to manage the state for synchronizing subscribers in MailerLite.
 */
class MailerLiteSyncState
{
    /** @var string String identifier for this instance of MailerLiteSyncState */
    protected string $syncStateId;

    protected int $fetchSubsOffset;

    /** @var int[] Array of CCN organization ids being processed */
    protected array $ccnOrgIds;

    /** @var int[] Array of MailerLite subscriber ids that need updating */
    protected array $updateSubscriberIds;

    /** @var string[] Array of Subscriber object hashes for subscribers to be added */
    protected array $addSubscribeHashes;

    /** @var GroupIdMap A map of group types to MailerLite group Ids. */
    protected GroupIdMap $mailerLiteGroupIds;

    /** @var SubscriberMap A map of Subscribers from CCN, keyed by email address */
    protected SubscriberMap $ccnSubscribers;

    /** @var EmailsMap A map of group types to a list of email addresses for that group */
    protected EmailsMap $emailsMap;

    private function __construct(string $stateId)
    {
        $this->syncStateId = $stateId;
        $this->fetchSubsOffset = 0;

        $this->ccnOrgIds = array();
        $this->updateSubscriberIds = array();
        $this->addSubscribeHashes = array();

        $this->ccnSubscribers = new SubscriberMap();
        $this->emailsMap = new EmailsMap();
        $this->mailerLiteGroupIds = new GroupIdMap();
    }

    /**
     * Generate a unique identifier for use as a syncStateId.
     *
     * @return string The identifier
     */
    public static function generateId(): string
    {
        return 'wscl_mlSyncState_'.strval(microtime(true));
    }

    /**
     * Fetch an instance for the specified syncStateId.
     *
     * If an instance does not already exist one is created.
     *
     * @param string $syncStateId The identifier for the sync state instance.
     *
     * @return MailerLiteSyncState An existing or new instance of MailerLiteSyncState
     */
    public static function instance(string $syncStateId): MailerLiteSyncState
    {
        /** @var MailerLiteSyncState|false */
        $state = get_transient($syncStateId);

        if (false === $state) {
            $state = new MailerLiteSyncState($syncStateId);
            $state->save();
        }

        return $state;
    }

    public function save(): void
    {
        \set_transient($this->syncStateId, $this, 60 * 60);
    }

    public function getStateId(): string
    {
        return $this->syncStateId;
    }

    public function resetFetchOffset(): void
    {
        $this->fetchSubsOffset = 0;
    }

    public function getFetchOffset(): int
    {
        return $this->fetchSubsOffset;
    }

    public function advanceFetchOffset(int $adv): void
    {
        $this->fetchSubsOffset += $adv;
    }

    /**
     * Fetch the name of the group currently being worked with.
     *
     * @return string|NULL
     */
    public function getCurrentGroup(): ?string
    {
        return $this->mailerLiteGroupIds->valid() ? $this->mailerLiteGroupIds->key() : null;
    }

    /**
     *
     * @return bool True if there are still groups in the list, otherwise false
     */
    public function advanceCurrentGroup(): bool
    {
        $this->mailerLiteGroupIds->next();

        return $this->mailerLiteGroupIds->valid();
    }

    /**
     *
     * @param int[] $orgIds
     */
    public function addOrgIds(array $orgIds): void
    {
        $this->ccnOrgIds = array_merge($this->ccnOrgIds, $orgIds);
    }

    public function removeOrgId(int $orgId): void
    {
        $this->ccnOrgIds = array_filter($this->ccnOrgIds, fn($entry) => $entry !== $orgId);
    }

    public function isFetchCcnSubscribersTasksComplete(): bool
    {
        return empty($this->ccnOrgIds);
    }

    /**
     *
     * @param int[] $subIds
     */
    public function addUpdateSubIds(array $subIds): void
    {
        $this->updateSubscriberIds = array_merge($this->updateSubscriberIds, $subIds);
    }

    public function markUpdateSubscriberComplete(int $subId): void
    {
        $this->updateSubscriberIds = array_filter($this->updateSubscriberIds, fn($entry) => $entry !== $subId);
    }

    public function isUpdateSubscribersTasksComplete(): bool
    {
        return empty($this->updateSubscriberIds);
    }

    /**
     *
     * @param string[] $subHashes
     */
    public function addNewSubsHashes(array $subHashes): void
    {
        $this->addSubscribeHashes = array_merge($this->addSubscribeHashes, $subHashes);
    }

    public function markAddSubscriberComplete(string $subHash): void
    {
        $this->addSubscribeHashes = array_filter($this->addSubscribeHashes, fn($entry) => $entry !== $subHash);
    }

    public function isAddSubscribersTasksComplete(): bool
    {
        return empty($this->addSubscribeHashes);
    }

    /**
     * Add additional MailerLite group that may need to be created.
     *
     * @param string $groupName
     * @param int $groupId
     */
    public function addMailerLiteGroup(string $groupName, int $groupId): void
    {
        $this->mailerLiteGroupIds->put($groupName, $groupId);
    }

    public function getMailerLiteGroupId(string $groupName): ?int
    {
        return $this->mailerLiteGroupIds->get($groupName);
    }

    /**
     * Add additional CCN subscribers to the list;
     *
     * @param CcnSubscriber[] $newSubs
     */
    public function addCcnSubscribers(array $newSubs): void
    {
        foreach ($newSubs as $newSub) {
            $existingSub = $this->ccnSubscribers->get($newSub->getEmail());

            if (is_null($existingSub)) {
                $this->ccnSubscribers->put($newSub->getEmail(), $newSub);
            } else {
                foreach ($newSub->getFields() as $field) {
                    if (is_null($existingSub->getField($field->getKey()))) {
                        $existingSub->addField($field);
                    }
                }
            }

            $this->emailsMap->put($newSub->getGroupName(), $newSub->getEmail());
        }
    }

    /**
     * Fetch the CCN Subscribers stored in the state.
     *
     * @return SubscriberMap A map of Subscriber instances, keyed by email address.
     */
    public function getCcnSubscribers(): SubscriberMap
    {
        return $this->ccnSubscribers;
    }

    public function &getEmailsMapRef(): EmailsMap
    {
        return $this->emailsMap;
    }
}
