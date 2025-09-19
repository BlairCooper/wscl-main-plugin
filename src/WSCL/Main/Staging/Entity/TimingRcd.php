<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

interface TimingRcd extends PersonInfoIntf
{
    /**
     * Fetch the identifier for the timing system.
     *
     * @return int The timing identifier.
     */
    public function getTimingSysId(): int;

    /**
     * Set the identifier for the timing system.
     *
     * @param int $timingSysId The timing identifier.
     */
    public function setTimingSysId(int $timingSysId): void;

    /**
     * Fetch the identifier for the registration system.
     *
     * @return int The registration identifier.
     */
    public function getRegSysId(): ?int;

    /**
     * Set the identifier for the registration system.
     *
     * @param int $regSysId The registration identifier.
     */
    public function setRegSysId(int $regSysId): void;

    /**
     * Fetch the Bib/Plate number for the rider
     *
     * @return int The bib number
     */
    public function getBibNumber(): int;

    /**
     * Set the Bib/Plate number for the rider
     *
     * @param int $bibNumber The bib number
     */
    public function setBibNumber(int $bibNumber): void;

    /**
     * Fetch the season points for the rider.
     *
     * @return int The season points.
     */
    public function getSeasonPoints(): int;

    /**
     * Set the season points for the rider.
     *
     * @param int $points The season points.
     */
    public function setSeasonPoints(int $points): void;

    /**
     * Fetch the last season's points for the rider.
     *
     * This would be the season before the current season.
     *
     * @return int The season points for last season.
     */
    public function getLastSeasonPoints(): int;

    /**
     * Set the points for last season.
     *
     * This would be the season before the current season.
     *
     * @param int $points A number of points.
     */
    public function setLastSeasonPoints(int $points): void;

    /**
     * Fetch the previous season's points for the rider.
     *
     * This would be the season prior to the last season, so two seasons ago.
     *
     * @return int The season points for the previous season.
     */
    public function getPreviousSeasonPoints(): int;

    /**
     * Set the points for previous season.
     *
     * This would be the season prior to the last season, so two seasons ago.
     *
     * @param int $points A number of points.
     */
    public function setPreviousSeasonPoints(int $points): void;

    /**
     * Set the first name.
     *
     * @param string $name The first name
     */
    public function setFirstName(string $name): void;

    /**
     * Set the last name.
     *
     * @param string $name The last name
     */
    public function setLastName(string $name): void;

    /**
     * Fetch the division for the rider.
     *
     * @return string The rider's division
     */
    public function getDivision(): string;

    /**
     * Set the division for a rider.
     *
     * @param string $division The rider's division
     */
    public function setDivision(string $division): void;

    /**
     * Fetch the staging score for the rider.
     *
     * @return float The staging score.
     */
    public function getStagingScore(): float;

    /**
     * Set the staging score for the rider.
     *
     * @param float $score The staging score.
     */
    public function setStagingScore(float $score): void;

    /**
     * Fetch the last season's staging score for the rider.
     *
     * This would be the season before the current season.
     *
     * @return float The staging score for last season.
     */
    public function getLastStagingScore(): float;

    /**
     * Set the staging score for last season.
     *
     * This would be the season before the current season.
     *
     * @param float $score A staging score.
     */
    public function setLastStagingScore(float $score): void;

    /**
     * Fetch the previous season's staging score for the rider.
     *
     * This would be the season prior to the last season, so two seasons ago.
     *
     * @return float The staging score for the previous season.
     */
    public function getPreviousStagingScore(): float;

    /**
     * Set the staging score for previous season.
     *
     * This would be the season prior to the last season, so two seasons ago.
     *
     * @param float $score A staging score.
     */
    public function setPreviousStagingScore(float $score): void;

    /**
     * Fetch the number of races the rider has competed in this season
     *
     * @return  int The number of races.
     */
    public function getRaceCnt(): int;

    /**
     * Set the race count for the current season
     *
     * @param int $cnt The number of races the rider has competed in this
     *      season.
     */
    public function setRaceCnt(int $cnt): void;

    /**
     * Fetch whether the rider has a first place finish
     *
     * @return bool True if the rider has a first place finish, otherwise false
     */
    public function getHasFirstPlaceFinish(): bool;

    /**
     * Set whether rider has a first place finish
     *
     * @param bool $hasFirstPlaceFinish True if the rider has a first place
     *      finish, otherwise false.
     */
    public function setHasFirstPlaceFinish(bool $hasFirstPlaceFinish): void;
}
