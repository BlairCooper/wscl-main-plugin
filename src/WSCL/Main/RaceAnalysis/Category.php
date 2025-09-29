<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceAnalysis;

use WSCL\Main\RaceResult\Entity\RiderTimingData;

class Category
{
    private const MANDATORY_UPGRAGE_CUTOFF = 10;
    private const MANDATORY_DOWNGRAGE_CUTOFF = 75;

    private string $name;

    /** @var RiderTimingData[] */
    private array $riders;

    private float $avgLapTime;

    private int $numberOfLaps;
    private int $catCurPromoteCutoff;
    private int $catCurRelegateCutoff;
    private int $catAboveCutoff;
    private int $catBelowCutoff;

    private ?Category $catAbove;
    private ?Category $catBelow;

    private float $mandatoryUpgradeCutoff;
    private float $mandatoryDowngradeCutoff;

    /**
     * Construct a Category instance.
     *
     * @param string $name The name of the category
     * @param int $catCurPromoteCutoff The cutoff within the current category,
     *      below which a rider may be promoted to the next category up.
     *      Should be 0 if there is not category above.
     * @param int $catCurRelegateCutoff The cutoff within the current
     *      category, over which a rider may be relegated to the next
     *      category down. Should be 0 if there is no category below.
     * @param int $catAboveCutoff The cutoff to the category above, below
     *      which a rider may be promoted to that category.
     * @param int $catBelowCutoff The cutoff to the category below, over
     *      which a rider may be relegated to that category. Should be 0 if
     *      there is not category below.
     */
    public function __construct(
        string $name,
        int $catCurPromoteCutoff,
        int $catCurRelegateCutoff,
        int $catAboveCutoff,
        int $catBelowCutoff
        )
    {
        if (
            ($catCurPromoteCutoff == 0 && $catAboveCutoff != 0) ||
            ($catCurPromoteCutoff != 0 && $catAboveCutoff == 0)
            ) {
            throw new \InvalidArgumentException('Promote cutoff and Above cutoff must both be 0 or not 0');
        }

        if (
            ($catCurRelegateCutoff == 0 && $catBelowCutoff != 0) ||
            ($catCurRelegateCutoff != 0 && $catBelowCutoff == 0)
            ) {
            throw new \InvalidArgumentException('Relegate cutoff and Below cutoff must both be 0 or not 0');
        }

        $this->name = $name;
        $this->catCurPromoteCutoff = $catCurPromoteCutoff;
        $this->catCurRelegateCutoff = $catCurRelegateCutoff;
        $this->catAboveCutoff = $catAboveCutoff;
        $this->catBelowCutoff = $catBelowCutoff;

        $this->riders = array();

        $this->catAbove = null;
        $this->catBelow = null;

        $this->avgLapTime = 0;
        $this->numberOfLaps = 0;
    }

    public function hasRiders(): bool
    {
        return !empty($this->riders);
    }

    /**
     *
     * @return RiderTimingData[] Array of timing data for each rider
     */
    public function getRiders(): array
    {
        return $this->riders;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAvgLapTime(): float
    {
        return $this->avgLapTime;
    }

    public function getCatAboveAvgLapTime(): float
    {
        return is_null($this->catAbove) ? 0 : $this->catAbove->getAvgLapTime();
    }

    public function getCatBelowAvgLapTime(): float
    {
        return is_null($this->catBelow) ? 0 : $this->catBelow->getAvgLapTime();
    }

    public function getCatAboveName(): string
    {
        return is_null($this->catAbove) ? '' : $this->catAbove->getName();
    }

    public function getCatBelowName(): string
    {
        return is_null($this->catBelow) ? '' : $this->catBelow->getName();
    }

    public function getNumberOfLaps(): int
    {
        return $this->numberOfLaps;
    }

    public function getPromotionCutoffCurCat(): int
    {
        return $this->catCurPromoteCutoff;
    }

    public function getPromotionCutoffAboveCat(): int
    {
        return $this->catAboveCutoff;
    }

    public function getRelegationCutoffCurCat(): int
    {
        return $this->catCurRelegateCutoff;
    }

    public function getRelegationCutoffBelowCat(): int
    {
        return $this->catBelowCutoff;
    }

    public function addRider(RiderTimingData $rider): void
    {
        $this->riders[] = $rider;
        $this->numberOfLaps = max($this->numberOfLaps, $rider->laps);
    }

    public function addCategories(?Category $catAbove, ?Category $catBelow): void
    {
        $this->catAbove = $catAbove;
        $this->catBelow = $catBelow;
    }

    private function calcMandatoryUpgradeCutoff(): void
    {
        $cutoffRiderOffset = intval(
            round(
                count($this->riders) * fdiv(self::MANDATORY_UPGRAGE_CUTOFF, 100),
                0,
                PHP_ROUND_HALF_DOWN
                )
        );

        $cutoffKey  = array_keys($this->riders)[$cutoffRiderOffset];
        $cutoffRider = $this->riders[$cutoffKey];

        $this->mandatoryUpgradeCutoff = $cutoffRider->averageLapSeconds;
    }

    private function calcMandatoryDowngradeCutoff(): void
    {
        $cutoffRiderOffset = intval(
            round(
                count($this->riders) * fdiv(self::MANDATORY_DOWNGRAGE_CUTOFF, 100),
                0,
                PHP_ROUND_HALF_UP
                )
        );

        // Small categories (e.g. 2) will not have enough riders to calculate a valid cutoffRiderOffset
        $cutoffKey  = array_keys($this->riders)[$cutoffRiderOffset] ?? count($this->riders) - 1;
        $cutoffRider = $this->riders[$cutoffKey];

        $this->mandatoryDowngradeCutoff = $cutoffRider->averageLapSeconds;
    }

    public function performCategoryCalcs(): void
    {
        if ($this->hasRiders()) {
            // Sort the riders by race time
            usort($this->riders, fn(RiderTimingData $a, RiderTimingData $b) => $a->raceTimeSeconds <=> $b->raceTimeSeconds);

            $totalTime = 0;
            $fastestTime = $this->riders[0]->raceTimeSeconds;

            foreach ($this->riders as $rider) {
                // Because some riders may not have completed all of the laps,
                // calculate their race time based on their average lap time
                // multiplied by the number of laps in the race
                $riderTime = $rider->averageLapSeconds * $this->numberOfLaps;

                $totalTime += $riderTime;

                $rider->percentOfFirst = round(($riderTime / $fastestTime) * 100, 1);
            }

            // Total time / number of riders to get average race time, / laps to get average lap time
            $this->avgLapTime = round(
                fdiv(
                    fdiv($totalTime, count($this->riders)),
                    $this->numberOfLaps
                    ),
                3);

            // Sort the riders by their average lap times
            usort($this->riders, fn(RiderTimingData $a, RiderTimingData $b) => $a->averageLapSeconds <=> $b->averageLapSeconds);

            $this->calcMandatoryUpgradeCutoff();
            $this->calcMandatoryDowngradeCutoff();
        }
    }

    public function performRiderCalcs(): void
    {
        foreach ($this->riders as $rider) {
            if (!is_null($this->catAbove)) {
                $rider->percentofCatAbove = round(
                    fdiv($rider->averageLapSeconds, $this->catAbove->getAvgLapTime()) * 100,
                    3
                    );

                $rider->mandatoryUpgrade =  $rider->averageLapSeconds < $this->catAbove->mandatoryUpgradeCutoff;
            }

            if (!is_null($this->catBelow)) {
                $rider->percentofCatBelow = round(
                    fdiv($rider->averageLapSeconds, $this->catBelow->getAvgLapTime()) * 100,
                    3
                    );
                $rider->mandatoryDowngrade = $rider->averageLapSeconds > $this->catBelow->mandatoryDowngradeCutoff;
            }

            $rider->promotionCandidate =
                // If a rider is too fast for their category
                (0 != $this->catCurPromoteCutoff && $rider->percentOfFirst < $this->catCurPromoteCutoff) ||
                // If a rider would be competitive in the category above
                (0 !== $this->catAboveCutoff && $rider->percentofCatAbove < $this->catAboveCutoff)
            ;

            $rider->relegationCandidate =
                // If a rider is too slow for their category
                (0 != $this->catCurRelegateCutoff && $rider->percentOfFirst > $this->catCurRelegateCutoff) &&
                // If a rider would be competitive in the category below
                (0 !== $this->catBelowCutoff && $rider->percentofCatBelow > $this->catBelowCutoff)
            ;
        }
    }
}
