<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;

class RaceRiderMap
{
    private string $raceTime;
    private RiderByFirstLastNameSet $riderSet;

    public function __construct(string $raceTime)
    {
        $this->raceTime = $raceTime;
        $this->riderSet = new RiderByFirstLastNameSet();
    }

    /**
     *
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->raceTime;
    }

    /**
     *
     * @param Rider $rider
     */
    public function addRider(Rider $rider): void
    {
        $this->riderSet->add($rider);
    }

    /**
     *
     * @return Rider[]
     */
    public function getRiders(): array
    {
        return $this->riderSet->getEntries();
    }
}
