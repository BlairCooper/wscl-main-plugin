<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Entity;

use RCS\Json\FieldPropertyEntry;
use RCS\Json\JsonDateTime;
use RCS\Json\JsonEntity;
use WSCL\Main\EventSeason;

class Event extends JsonEntity
{
    public int $id;
    public string $name;
    public JsonDateTime $date;
    public EventAttributes $attributes;

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

    public function isSpringRace(): bool
    {
        $midYear = new \DateTime($this->date->format('Y') . '-07-01');

        return $midYear > $this->date;
    }

    /**
     * Is the event in the era of High School 1 (Beg) & 2 (Int)?
     *
     * @return bool True if the event used HS1 (Beg)/2 (Int), otherwise false.
     */
    public function isHighSchoolxTransitionEra(): bool
    {
        $eraStart = new \DateTime('2024-01-01');
        $eraEnd = new \DateTime('2024-12-31');

        return $this->date >= $eraStart && $this->date <= $eraEnd;
    }

    /**
     * Is the event in the era of High School 1 & 2?
     *
     * @return bool True if the event used HS1/2, otherwise false.
     */
    public function isHighSchoolxEra(): bool
    {
        $eraStart = new \DateTime('2025-01-01');

        return $this->date >= $eraStart;
    }

    public function isFallRace(): bool
    {
        return !$this->isSpringRace();
    }

    public function getEventSeason(\DateTime $relativeDate = new \DateTime()): EventSeason
    {
        $eventSeason = EventSeason::EARLIER;

        $relativeYear = intval($relativeDate->format('Y'));
        $preceedingYear = $relativeYear - 1;
        $raceYear = intval($this->date->format('Y'));
        $springDate = new \DateTime($relativeYear . '-07-01') > $relativeDate;
        $isSpringRace = $this->isSpringRace();

        switch ($raceYear) {
            case $relativeYear:
                $eventSeason = $this->getEventSeasonCurrentYear($springDate, $isSpringRace);
                break;

            case $preceedingYear;
                $eventSeason = $this->getEventSeasonLastYear($springDate, $isSpringRace);
                break;

            default;
                if ($raceYear > $relativeYear) {
                    $eventSeason = EventSeason::FUTURE;
                }
                break;
        }

        return $eventSeason;
    }

    private function getEventSeasonCurrentYear(bool $springDate, bool $isSpringRace): EventSeason
    {
        if ($springDate && $isSpringRace || !$springDate && !$isSpringRace) {
            $eventSeason = EventSeason::CURRENT;
        }
        else {
            if (!$springDate && $isSpringRace) {
                $eventSeason = EventSeason::LAST;
            } else {
                $eventSeason = EventSeason::FUTURE;
            }
        }

        return $eventSeason;
    }

    private function getEventSeasonLastYear (bool $springDate, bool $isSpringRace): EventSeason
    {
        if ($springDate) {
            if ($isSpringRace) {
                $eventSeason = EventSeason::PREVIOUS;
            } else {
                $eventSeason = EventSeason::LAST;
            }
        } else {
            if ($isSpringRace) {
                $eventSeason = EventSeason::EARLIER;
            } else {
                $eventSeason = EventSeason::PREVIOUS;
            }
        }

        return $eventSeason;
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
     * Wrapper around new DateTime() to allow for overriding during unit tests.
     *
     * @return \DateTime
     */
    protected function getCurrentDateTime(): \DateTime
    {
        return new \DateTime();
    }

    /**
     *
     * @return string[]
     */
    public static function getAdditionalFields(): array
    {
        return [
            'EventName',
            'EventDate'
        ];
    }

    /**
     * {@inheritDoc}
     * @see \RCS\Json\JsonEntity::getFieldPropertyMap()
     */
    protected static function getFieldPropertyMap(): array
    {
        static $fields = array (
            new FieldPropertyEntry('ID', 'id'),
            new FieldPropertyEntry('EventName', 'name'),
            new FieldPropertyEntry('EventDate', 'date'),
        );

        return $fields;
    }
}

/*
    Default fields
    "ID": "48568",
    "UserID": 24205,
    "UserName": "WashingtonStudent",
    "FileName": "48568.ses",
    "FilePath": "",
    "CheckedOut": false,
    "Participants": 223,
    "NotActivated": 223,

    Additional Fields (AddSettings param)
    "EventName": "Blast From The Past",
    "EventDate": "2016-01-30",
    "RegActive": false,
    "EventLogo": "",
    "EventType": 0,
    "TestMode": false
*/
