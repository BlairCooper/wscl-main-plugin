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
     * @param \DateTime $raceTime
     * @param Rider $rider
     */
    public function addRider(\DateTime $raceTime, Rider $rider): void
    {
        $mapKey = $raceTime->format('c');

        if (!array_key_exists($mapKey, $this->raceTimeMap)) {
            $this->raceTimeMap[$mapKey] = new RaceRiderMap($raceTime);
        }

        $this->raceTimeMap[$mapKey]->addRider($rider);
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
