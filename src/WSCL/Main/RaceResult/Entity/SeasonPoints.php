<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Entity;

use RCS\Json\FieldPropertyEntry;
use RCS\Json\JsonEntity;

class SeasonPoints extends JsonEntity
{
    public int $id;
    public int $bib;
    public int $regSysId;
    public int $seasonPoints;
    public string $firstname;
    public string $lastname;
    public string $gender;
    public \DateTime $dateOfBirth;
    public float $stagingScore;
    public int $raceCnt;
    public bool $hasFirstPlaceFinish;
    public String $division;

    /**
     *
     * Mapping of RaceResult fields to class properties.
     *
     * {@inheritDoc}
     * @see \RCS\Json\JsonEntity::getFieldPropertyMap()
     */
    protected static function getFieldPropertyMap(): array
    {
        static $fields = array (
            new FieldPropertyEntry('ID', 'id'),
            new FieldPropertyEntry('Bib', 'bib'),
            new FieldPropertyEntry('RegSysID', 'regSysId'),
            new FieldPropertyEntry('IndividualPointsSeason', 'seasonPoints'),
            new FieldPropertyEntry('Firstname', 'firstname'),
            new FieldPropertyEntry('Lastname', 'lastname'),
            new FieldPropertyEntry('Sex', 'gender'),
            new FieldPropertyEntry('DateOfBirth', 'dateOfBirth'),
            new FieldPropertyEntry('StagingScoreSeason.Decimal', 'stagingScore'),
            new FieldPropertyEntry('fnStagingScoreRacedCnt', 'raceCnt'),
            new FieldPropertyEntry('fnHasFirstPlaceFinish', 'hasFirstPlaceFinish'),
            new FieldPropertyEntry('Division', 'division')
        );

        return $fields;
    }

    /**
     * {@inheritDoc}
     * @see \RCS\Json\JsonEntity::isMapByIndex()
     */
    protected static function isMapByIndex(): bool
    {
        return true;
    }
}
