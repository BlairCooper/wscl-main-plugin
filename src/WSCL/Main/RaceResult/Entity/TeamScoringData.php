<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Entity;

use RCS\Json\FieldPropertyEntry;
use RCS\Json\JsonEntity;

class TeamScoringData extends JsonEntity
{
    // Fields read from RaceResult in the same order as fieldPropertyMap below
    public int $bib;
    public int $status;
    public string $statusText;
    public bool $started;
    public bool $finished;
    public string $fullname;
    public string $team;
    public string $division;
    public string $gender;
    public string $category;

    public int $teamRankTop3;
    public int $teamRankTop5;

    public int $teamScoreTop3;
    public int $teamScoreTop5;
    /**
     * {@inheritDoc}
     * @see \RCS\Json\JsonEntity::getFieldPropertyMap()
     */
    protected static function getFieldPropertyMap(): array
    {
        static $fields = array(
            new FieldPropertyEntry('Bib', 'bib'),
            new FieldPropertyEntry('Status', 'status'),
            new FieldPropertyEntry('StatusText', 'statusText'),
            new FieldPropertyEntry('Started', 'started'),
            new FieldPropertyEntry('Finished', 'finished'),
            new FieldPropertyEntry('DisplayFullName', 'fullname'),
            new FieldPropertyEntry('Club', 'team'),
            new FieldPropertyEntry('Division', 'division'),
            new FieldPropertyEntry('Gender', 'gender'),
            new FieldPropertyEntry('Contest.Name', 'category'),
            new FieldPropertyEntry('TS_Top3_CurrentRace.Rank', 'teamRankTop3'),
            new FieldPropertyEntry('TS_Top3_CurrentRace.Time1', 'teamScoreTop3'),
            new FieldPropertyEntry('TS_Top5_CurrentRace.Rank', 'teamRankTop5'),
            new FieldPropertyEntry('TS_Top5_CurrentRace.Time1', 'teamScoreTop5'),
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
