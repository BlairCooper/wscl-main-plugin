<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Entity;

use RCS\Json\FieldPropertyEntry;
use RCS\Json\JsonEntity;

class RiderTimingData extends JsonEntity
{
    // Fields read from RaceResult in the same order as fieldPropertyMap below
    public int $rank;
    public int $bib;
    public int $status;
    public string $statusText;
    public string $fullname;
    public string $team;
    public int $grade;
    public int $laps;
    public string $fastestLap;
    public string $averageLap;
    public string $gender;
    public string $category;
    public string $raceTime;
    public float $raceTimeSeconds;
    public float $averageLapSeconds;
    public float $gapToFirst;

    // Additional fields, not read from RaceResult
    public float $percentOfFirst;
    public float $percentofCatAbove = 0;
    public float $percentofCatBelow = 0;

    public bool $promotionCandidate = false;
    public bool $relegationCandidate = false;

    public bool $mandatoryUpgrade = false;
    public bool $mandatoryDowngrade = false;

    /**
     * {@inheritDoc}
     * @see \RCS\Json\JsonEntity::getFieldPropertyMap()
     */
    protected static function getFieldPropertyMap(): array
    {
        static $fields = array(
            new FieldPropertyEntry('OverallRank', 'rank'),
            new FieldPropertyEntry('Bib', 'bib'),
            new FieldPropertyEntry('Status', 'status'),
            new FieldPropertyEntry('StatusText', 'statusText'),
            new FieldPropertyEntry('DisplayFullName', 'fullname'),
            new FieldPropertyEntry('Club', 'team'),
            new FieldPropertyEntry('Grade', 'grade'),
            new FieldPropertyEntry('NumberOfLaps', 'laps'),
            new FieldPropertyEntry('FastestLap', 'fastestLap'),
            new FieldPropertyEntry('AverageLap', 'averageLap'),
            new FieldPropertyEntry('Gender', 'gender'),
            new FieldPropertyEntry('Contest.Name', 'category'),
            new FieldPropertyEntry('TimeOrStatus', 'raceTime'),
            new FieldPropertyEntry('fnTimeOrStatus', 'raceTime'),
            new FieldPropertyEntry('Finish.Decimal', 'raceTimeSeconds'),
            new FieldPropertyEntry('AverageLap.Decimal', 'averageLapSeconds'),
            // Result ID 14, Finish Time; Rank ID 1, OverallRank
            new FieldPropertyEntry('GapTimeTop(14;1;"";"sssss.kkk")', 'gapToFirst'),
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
