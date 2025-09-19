<?php
declare(strict_types = 1);
namespace WSCL\Main\Petitions;

use RCS\Traits\SingletonTrait;

/**
 * This class tracks whether an Approval or Denial notification has been
 * triggered for a given petition request.
 */
class PetitionNotifications
{
    private const PETITION_TRIGGER_OPTION = 'wsclPetitionNotifications';

    /**
     * Associative array of petition request entry ids to an array. The array
     * is an associative array of PetitionNotificationEvent to boolean.
     *
     * @var array<int, array<string, bool>>
     */
    private array $triggers = [];

    use SingletonTrait;

    protected function initializeInstance (): void
    {
        $this->triggers = get_option(self::PETITION_TRIGGER_OPTION, []);
    }

    private function save(): void
    {
        update_option(self::PETITION_TRIGGER_OPTION, $this->triggers);
    }

    /**
     * Resets the notification triggers for a given petition request, meaning
     * the assumption will be that no triggers have occurred for the request.
     *
     * @param int $entryId The entry id for a petition request
     */
    public function reset(int $entryId): void
    {
        unset($this->triggers[$entryId]);
        $this->save();
    }

    /**
     * Record that an approval has been triggered for a given petition
     * request.
     *
     * @param int $entryId The entry id for the petition request
     */
    public function approvalTriggered(int $entryId): void
    {
        $this->eventTriggered($entryId, PetitionNotificationEvent::APPROVAL);
    }

    /**
     * Record that a deinal has been triggered for a given petition request.
     *
     * @param int $entryId The entry id for the petition request
     */
    public function denialTriggered(int $entryId): void
    {
        $this->eventTriggered($entryId, PetitionNotificationEvent::DENIAL);
    }

    private function eventTriggered(int $entryId, PetitionNotificationEvent $event): void
    {
        if (!isset($this->triggers[$entryId])) {
            $this->triggers[$entryId] = [
                PetitionNotificationEvent::APPROVAL->value => false,
                PetitionNotificationEvent::DENIAL->value => false
            ];
        }

        $this->triggers[$entryId][$event->value] = true;

        $this->save();
    }

    /**
     * Check if an approval has been previously triggered for a given
     * petition request.
     *
     * @param int $entryId The entry id for the petition request
     */
    public function wasApprovalTriggered(int $entryId): bool
    {
        return $this->wasTriggered($entryId, PetitionNotificationEvent::APPROVAL);
    }

    /**
     * Check if a deinal has been previously triggered for a given petition
     * request.
     *
     * @param int $entryId The entry id for the petition request
     */
    public function wasDenialTriggered(int $entryId): bool
    {
        return $this->wasTriggered($entryId, PetitionNotificationEvent::DENIAL);
    }

    private function wasTriggered(int $entryId, PetitionNotificationEvent $event): bool
    {
        $wasTriggered = false;

        if (isset($this->triggers[$entryId])) {
            $wasTriggered = $this->triggers[$entryId][$event->value];
        }

        return $wasTriggered;
    }
}

/**
 * Enumeration of possible petition notification events.
 */
enum PetitionNotificationEvent: string {
    case APPROVAL = 'Approved';
    case DENIAL = 'Denied';
}
