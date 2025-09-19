<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceAnalysis;

use WSCL\Main\RaceResult\Entity\TeamScoringData;

class Team
{
    private string $name;

    private int $riderCnt;
    private int $startedCnt;
    private int $finishedCnt;
    private int $femaleCnt;
    private int $maleCnt;

    private int $rankTop3;
    private int $rankTop5;
    private int $pointsTop3;
    private int $pointsTop5;

    /** @var array<int, int> */
    private array $categoryCnt;

    public function __construct(string $name)
    {
        $this->name = $name;

        $this->riderCnt = 0;
        $this->startedCnt = 0;
        $this->finishedCnt = 0;
        $this->femaleCnt = 0;
        $this->maleCnt = 0;

        $this->categoryCnt = array();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addRider(TeamScoringData $rider): void
    {
        $this->riderCnt++;

        $catgoryNdx = substr($rider->category, 0, 1);

        if ($rider->started) {
            $this->startedCnt++;
            $this->categoryCnt[$catgoryNdx] = ($this->categoryCnt[$catgoryNdx] ?? 0) + 1;
        }

        if ($rider->finished) {
            $this->finishedCnt++;
        }

        if ($rider->gender == 'f') {
            $this->femaleCnt++;
        } else {
            $this->maleCnt++;
        }
    }

    public function getRiderCount(): int
    {
        return $this->riderCnt;
    }

    public function getStartedCount(): int
    {
        return $this->startedCnt;
    }

    public function getFinishedCount(): int
    {
        return $this->finishedCnt;
    }

    public function setTop3Rank(int $rank): void
    {
        $this->rankTop3 = $rank;
    }

    public function getTop3Rank(): int
    {
        return $this->rankTop3;
    }

    public function setTop5Rank(int $rank): void
    {
        $this->rankTop5 = $rank;
    }

    public function getTop5Rank(): int
    {
        return $this->rankTop5;
    }

    public function setTop3Points(int $points): void
    {
        $this->pointsTop3 = $points;
    }

    public function getTop3Points(): int
    {
        return $this->pointsTop3;
    }

    public function setTop5Points(int $points): void
    {
        $this->pointsTop5 = $points;
    }

    public function getTop5Points(): int
    {
        return $this->pointsTop5;
    }

    public function getFemaleCount(): int
    {
        return $this->femaleCnt;
    }

    public function getMaleCount(): int
    {
        return $this->maleCnt;
    }

    public function getCategoryCount(string $ndx): int
    {
        return $this->categoryCnt[$ndx] ?? 0;
    }
}
