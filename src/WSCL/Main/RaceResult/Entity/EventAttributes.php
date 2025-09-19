<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Entity;

use RCS\Json\FieldPropertyEntry;
use RCS\Json\JsonEntity;
use RCS\Util\ReflectionHelper;
use WSCL\Main\EventSeason;

class EventAttributes extends JsonEntity
{
    public int $season;
    public bool $isChampionshipSeason;
    public bool $isStateChampionships;
    public bool $isStagingSet;
    public bool $isCoachesRace;
    public bool $isRelayRace;
    public EventSeason $eventSeason;

    public static function getClassFactory() : callable
    {
        return static function (string $string): EventAttributes
        {
            $attrs = new EventAttributes();

            $json = json_decode($string);

            foreach (self::getFieldPropertyMap() as $entry) {
                if (isset($json->{$entry->getField()})) {
                    $typeName = ReflectionHelper::getPropertyType(EventAttributes::class, $entry->getProperty());

                    switch ($typeName) {
                        case 'string':
                            $value = $json->{$entry->getField()};
                            break;
                        case 'int':
                            $value = intval($json->{$entry->getField()});
                            break;
                        case 'bool':
                            $value = intval($json->{$entry->getField()}) ? true : false;
                            break;

                        default:
                            $value = $json->{$entry->getField()};
                            break;
                    }

                    $attrs->{$entry->getProperty()} = $value;
                }
            }

            return $attrs;
        };
    }

    public function isChampionshipSeason(): bool
    {
        return $this->isChampionshipSeason ?? false;
    }

    public function isCoachesRace(): bool
    {
        return $this->isCoachesRace ?? false;
    }

    public function isRelayRace(): bool
    {
        return $this->isRelayRace ?? false;
    }

    public function isStagingSet(): bool
    {
        return $this->isStagingSet ?? false;
    }

    public function isStateChampionship(): bool
    {
        return $this->isStateChampionships ?? false;
    }

    public function getSeason(): string
    {
        return strval($this->season);
    }

    public function getEventSeason(): ?EventSeason
    {
        return $this->eventSeason ?? null;
    }

    /**
     * {@inheritDoc}
     * @see \RCS\Json\JsonEntity::getFieldPropertyMap()
     */
    protected static function getFieldPropertyMap(): array
    {
        static $fields = array (
            new FieldPropertyEntry('Season', 'season'),
            new FieldPropertyEntry('IsChampionshipSeason', 'isChampionshipSeason'),
            new FieldPropertyEntry('IsStateChamps', 'isStateChampionships'),
            new FieldPropertyEntry('IsStagingSet', 'isStagingSet'),
            new FieldPropertyEntry('IsCoachesRace', 'isCoachesRace'),
            new FieldPropertyEntry('IsRelayRace', 'isRelayRace')
        );

        return $fields;
    }
}
