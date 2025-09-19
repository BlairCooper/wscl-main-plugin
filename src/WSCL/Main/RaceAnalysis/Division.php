<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceAnalysis;

use WSCL\Main\RaceResult\Entity\TeamScoringData;

class Division
{
    private string $name;

    /** @var array<string, Team> */
    private array $teamMap;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->teamMap = array();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addRider(TeamScoringData $rider): void
    {
        $team = $this->teamMap[$rider->team] ?? null;

        if (!isset($team)) {
            $team = new Team($rider->team);
            $team->setTop3Rank($rider->teamRankTop3);
            $team->setTop5Rank($rider->teamRankTop5);
            $team->setTop3Points($rider->teamScoreTop3);
            $team->setTop5Points($rider->teamScoreTop5);

            $this->teamMap[$team->getName()] = $team;
        }

        $team->addRider($rider);
    }

    public function getTeamCount(): int
    {
        return count($this->teamMap);
    }

    /**
     *
     * @return Team[]
     */
    public function getTeams(): array
    {
        uasort($this->teamMap, array($this, 'compare'));

        return $this->teamMap;
    }

    private function compare(Team $t1, Team $t2): int
    {
        if (preg_match('/.*D2$/', $this->name)) {
            $result = $t1->getTop3Rank() <=> $t2->getTop3Rank();
        } else {
            $result = $t1->getTop5Rank() <=> $t2->getTop5Rank();
        }

        return $result;
    }
}
