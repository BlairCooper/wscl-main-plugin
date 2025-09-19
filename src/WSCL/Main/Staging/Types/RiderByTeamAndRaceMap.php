<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;
use WSCL\Main\Staging\Models\Event;

class RiderByTeamAndRaceMap
{
    private const RACE_TIME_FORMAT = 'h:i A';

    private Event $event;

    /** @var TeamRaceMap[] */
    private array $teamMap = array();

    /**
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     *
     * @param Rider $rider
     */
    public function add(Rider $rider): void
    {
        $team = $rider->getTeam();
        if (!array_key_exists($team, $this->teamMap)) {
            $this->teamMap[$team] = new TeamRaceMap($team);
        }

        $teamEntry = $this->teamMap[$team];

        $raceTime = $this->getRiderRaceTime($rider, $this->event);
        $teamEntry->addRider($raceTime, $rider);
    }

    /**
     *
     * @return TeamRaceMap[]
     */
    public function getTeamEntries(): array
    {
        ksort($this->teamMap);

        return $this->teamMap;
    }

    private function getRiderRaceTime(Rider $rider, Event $event): string
    {
        $raceTime = '';

        $category = $event->getCategoryByName($rider->getCategory());

        if (isset($category)) {
            foreach ($event->getRaces() as $race) {
                if ($race->hasCategory($category->id)) {
                    $raceTime = $race->getStartTime()->format(self::RACE_TIME_FORMAT);
                    break;
                }
            }
        }

        return $raceTime;
    }

/*
    public function getRaceEntries(string $team): array
    {
        $result = array();

        if (isset($this->teams[$team])) {
            $result = $this->teams[$team];
        }

        return $result;
    }

    public function getRaceRiders(string $team, string $startTime): array
    {
        $result = array();

        $raceEntries = $this->getRaceEntries($team);

        if (!empty($raceEntries) && isset($raceEntries[$startTime])) {
            $result = $raceEntries[$startTime];

            usort($result, array($this, 'compare'));
        }

        return $result;
    }

    private function compare(Rider $r1, Rider $r2): int
    {
        $result = $r1->getFirstName() <=> $r2->getFirstName();

        if (0 == $result) {
            $result = $r1->getLastName() <=> $r2->getLastName();
        }

        return $result;
    }

*/
}
