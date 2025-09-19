<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Models;

use RCS\Json\JsonDateTime;


class Event // NOSONAR - ignore too many methods
{
    public int $id;             // The identifier for this event
    public int $rrEventId;      // The RaceResult event id for this event
    public int $rrLastEventId;  // The RaceResult event id to use for "last season"
    public int $rrPrevEventId;  // The RaceResult event id to use for "previous season", the one before last season
    public string $name;        // Used for title in output
    public JsonDateTime $date;
    public int $ridersPerRow = 5;
    public int $attendanceFactor = 100;
    public int $doubleUpRowsPoints = -1;
    public bool $seasonFirstEvent = false;
    public int $divisionCnt = 1;

    private int $riderCnt = 0;

    /** @var Race[] */
    public array $races = array();

    /** @var Category[] */
    public array $categories = array();

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getRaceResultEventId(): ?int
    {
        return $this->rrEventId ?? null;
    }

    public function setRaceResultEventId(int $id): void
    {
        $this->rrEventId = $id;
    }

    public function getRaceResultLastSeasonEventId(): ?int
    {
        return $this->rrLastEventId ?? null;
    }

    public function setRaceResultLastSeasonEventId(int $id): void
    {
        $this->rrLastEventId = $id;
    }

    public function getRaceResultPrevSeasonEventId(): ?int
    {
        return $this->rrPrevEventId ?? null;
    }

    public function setRaceResultPrevSeasonEventId(int $id): void
    {
        $this->rrPrevEventId = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRidersPerRow(): int
    {
        return $this->ridersPerRow;
    }

    public function getAttendanceFactor(): int
    {
        return $this->attendanceFactor;
    }

    public function getDoubleUpRowsPoints(): int
    {
        return $this->doubleUpRowsPoints;
    }

    public function getCategory(int $catId): ?Category
    {
        return $this->fetchCategoryById($catId);
    }

    public function getCategoryByName(string $catName): ?Category
    {
        return $this->fetchCategoryByName($catName);
    }

    public function getRaceDate(): \DateTime
    {
        return $this->date;
    }

    public function getRaceDateAsString(): string
    {
        return $this->date->format('F j, Y');   // September 26, 2021
    }

    public function isFirstSeasonEvent(): bool
    {
        return $this->seasonFirstEvent;
    }

    public function setFirstSeasonEvent(bool $isFirstEvent): void
    {
        $this->seasonFirstEvent = $isFirstEvent;
    }

    public function getDivisionCnt(): int
    {
        return $this->divisionCnt;
    }

    public function setDivisionCnt(int $cnt): void
    {
        $this->divisionCnt = $cnt;
    }

    public function addCategory(Category &$category): bool
    {
        $result = false;

        if (null === $this->fetchCategoryByName($category->getName())) {
            $category->setId($this->fetchNextCategoryId());
            array_push($this->categories, $category);
            $result = true;
        }

        return $result;
    }

    public function updateCategory(int $catId, Category $updateCat): bool
    {
        $result = false;

        $existingCat = $this->fetchCategoryById($catId);
        if (null !== $existingCat) {
            $existingCat->update($updateCat);
            $result = true;
        }

        return $result;
    }

    public function deleteCategory(int $catId): ?Category
    {
        $existingCat = $this-> fetchCategoryById($catId);

        if (null != $existingCat) {
            $this->categories = array_values(
                array_filter(
                    $this->categories,
                    fn(Category $category): bool => $category->getId() !== $catId
                    )
                );
            // Delete the category from any races
            foreach ($this->races as $race) {
                $race->removeCategory($catId);
            }
        }

        return $existingCat;
    }

    public function getRace(int $raceId): ?Race
    {
        return $this->fetchRaceById($raceId);
    }

    public function addRace(Race &$newRace): bool
    {
        $result = false;

        if (null === $this->fetchRaceByStartTime($newRace->startTime)) {
            $newRace->setId($this->fetchNextRaceId());

            $this->removeInvalidCategories($newRace);
            $this->removeCategoriesFromRaces($newRace->getCategories());

            // Set date on staging and start times to match event dDate
            $newRace->stagingTime->setDate(
                intval($this->date->format('Y')),
                intval($this->date->format('m')),
                intval($this->date->format('d'))
                );
            $newRace->startTime->setDate(
                intval($this->date->format('Y')),
                intval($this->date->format('m')),
                intval($this->date->format('d'))
                );

            array_push($this->races, $newRace);
            $result = true;
        }

        return $result;
    }

    public function updateRace(int $raceId, Race $updateRace): bool
    {
        $result = false;

        $existingRace = $this->fetchRaceById($raceId);

        if (null !== $existingRace) {
            $this->removeInvalidCategories($updateRace);

            if ($existingRace !== $updateRace) {
                $this->removeCategoriesFromRaces($updateRace->getCategories());
            }

            $existingRace->update($updateRace);
            $result = true;
        }

        return $result;
    }

    public function deleteRace(int $raceId): ?Race
    {
        $existingRace = $this->fetchRaceById($raceId);

        if (null != $existingRace) {
            $this->races = array_values(
                array_filter(
                    $this->races,
                    fn(Race $race): bool => $race->getId() !== $raceId
                )
            );
        }

        return $existingRace;
    }

    /**
     * Fetch the races for the event.
     *
     * Races are returned in the order specified by the categoryIds property.
     *
     * @return Race[] An array of Race instances
     */
    public function getRaces(): array
    {
        return $this->races;
    }

    /**
     *
     * @param int $raceId
     *
     * @return Category[]
     */
    public function getRaceCategories(int $raceId): array
    {
        $result = array();

        $race = $this->fetchRaceById($raceId);

        if (isset($race)) {
            $catIds = $race->getCategories();

            foreach ($catIds as $catId) {
                $result = array_merge(
                    $result,
                    array_filter($this->categories, fn(Category $cat) => $cat->getId() == $catId)
                );
            }
        }

        return $result;
    }

    public function setRiderCnt(int $cnt): void
    {
        $this->riderCnt = $cnt;
    }

    public function getRiderCnt(): int
    {
        return $this->riderCnt;
    }

    private function fetchCategoryByName(string $name): ?Category
    {
        $result = null;

        foreach ($this->categories as $category) {
            if ($category->getName() == $name) {
                $result = $category;
                break;
            }
        }

        return $result;
    }

    private function fetchCategoryById(int $catId): ?Category
    {
        $result = null;

        foreach ($this->categories as $category) {
            if ($category->getId() == $catId) {
                $result = $category;
                break;
            }
        }

        return $result;
    }

    private function fetchNextCategoryId(): int
    {
        $result = 0;

        foreach ($this->categories as $category) {
            $result = max($category->getId(), $result);
        }

        return $result + 1;
    }

    private const TIME_OF_DAY_FORMAT = 'h:i:s A';

    private function fetchRaceByStartTime(\DateTime $startTime): ?Race
    {
        $result = null;

        $inTimeStr = $startTime->format(self::TIME_OF_DAY_FORMAT);

        foreach ($this->races as $race) {
            if ($race->startTime->format(self::TIME_OF_DAY_FORMAT) == $inTimeStr) {
                $result = $race;
                break;
            }
        }

        return $result;
    }

    private function fetchRaceById(int $raceId): ?Race
    {
        $result = null;

        foreach ($this->races as $race) {
            if ($race->getId() == $raceId) {
                $result = $race;
                break;
            }
        }

        return $result;
    }

    private function fetchNextRaceId(): int
    {
        $result = 0;

        foreach ($this->races as $race) {
            $result = max($race->getId(), $result);
        }

        return $result + 1;
    }

    /**
     *
     * @return int[]
     */
    private function fetchCategoryIds(): array
    {
        $result = array();

        foreach ($this->categories as $category) {
            array_push($result, $category->getId());
        }

        return $result;
    }

    /**
     * Remove any categories from a race that aren't valid.
     *
     * @param Race $race The race to inspect.
     */
    private function removeInvalidCategories(Race $race): void
    {
        $catIds = $this->fetchCategoryIds();

        $race->setCategories(
            array_values(
                array_filter($race->categoryIds, fn($catId): bool => in_array($catId, $catIds))
                )
            );
    }

    /**
     * Remove any categories in the new race from existing races.
     *
     * @param int[] $catIds The category ids to remove.
     */
    private function removeCategoriesFromRaces(array $catIds): void
    {
        foreach ($this->races as $race) {
            $race->removeCategories($catIds);
        }
    }
}
