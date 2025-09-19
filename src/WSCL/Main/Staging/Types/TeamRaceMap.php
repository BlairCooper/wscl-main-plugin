<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;

class TeamRaceMap
{
    private string $team;

    /** @var RaceRiderMap[] */
    private array $raceTimeMap = array();

    public function __construct(string $team)
    {
        $this->team = $team;
    }

    /**
     *
     * @return string
     */
    public function getTeam(): string
    {
        return $this->team;
    }

    /**
     *
     * @param string $raceTime
     * @param Rider $rider
     */
    public function addRider(string $raceTime, Rider $rider): void
    {
        if (!array_key_exists($raceTime, $this->raceTimeMap)) {
            $this->raceTimeMap[$raceTime] = new RaceRiderMap($raceTime);
        }

        $this->raceTimeMap[$raceTime]->addRider($rider);
    }

    /**
     *
     * @return RaceRiderMap[]
     */
    public function getRaces(): array
    {
        ksort($this->raceTimeMap);

        return $this->raceTimeMap;
    }
}
