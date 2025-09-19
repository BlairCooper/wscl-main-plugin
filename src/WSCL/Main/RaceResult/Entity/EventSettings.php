<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Entity;

use RCS\Json\FieldPropertyEntry;
use RCS\Json\JsonEntity;

class EventSettings extends JsonEntity
{
    public int $id;
    public string $name;
    public \DateTime $date;
    public EventAttributes $attributes;

    public function setId(int $eventId): void
    {
        $this->id = $eventId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function isCoachesRace(): bool
    {
        return $this->attributes->isCoachesRace();
    }

    public function isRelayRace(): bool
    {
        return $this->attributes->isRelayRace();
    }

    public function isStagingSet(): bool
    {
        return $this->attributes->isStagingSet();
    }

    public function isStateChampionship(): bool
    {
        return $this->attributes->isStateChampionship();
    }

    public function getSeason(): ?string
    {
        return $this->attributes->getSeason();
    }

    /**
     * {@inheritDoc}
     * @see \RCS\Json\JsonEntity::getFieldPropertyMap()
     */
    protected static function getFieldPropertyMap(): array
    {
        static $fields = array (
            new FieldPropertyEntry('EventName', 'name'),
            new FieldPropertyEntry('EventDate', 'date'),
            new FieldPropertyEntry('EventAttributes', 'attributes')
        );

        return $fields;
    }
}
