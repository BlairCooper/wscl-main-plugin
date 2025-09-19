<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;

/**
 * Implements a mapping of categories to array of riders.
 */
class CategoryMap
{
    /** @var array<string, array<Rider>> */
    private array $categoryMap = array();

    /**
     * Check if the map has any riders.
     *
     * @return bool true if the map is empty, otherwise false
     */
    public function isEmpty(): bool
    {
        return empty($this->categoryMap);
    }

    /**
     *
     * @param string $category
     *
     * @return Rider[]
     */
    public function &get(string $category): array
    {
        if (!array_key_exists($category, $this->categoryMap)) {
            $this->categoryMap[$category] = [];
        }

        return $this->categoryMap[$category];
    }

    /**
     *
     * @return array<string, array<Rider>>
     */
    public function getMap(): array
    {
        return $this->categoryMap;
    }

    public function addRider(string $category, Rider $rider): void
    {
        if (!array_key_exists($category, $this->categoryMap)) {
            $this->categoryMap[$category] = [];
        }

        array_push($this->categoryMap[$category], $rider);
    }

    /**
     * Fetch the riders for a category, sorted by ranking.
     *
     * Rank is determined by:
     * <li>current season points
     * <li>last season points, for ties in current season points
     * <li>randomly, for ties in last season points
     *
     * @param string $category The category to return
     *
     * @return Rider[] An array of Riders for the category.
     */
    public function getRidersByCategory(string $category): array
    {
        $riders = $this->get($category);

        usort($riders, array($this, 'compareByStagingScore'));

        return $riders;
    }

    private function compareByStagingScore(Rider $r1, Rider $r2): int
    {
        $result = $r2->hasFirstPlaceFinish() <=> $r1->hasFirstPlaceFinish();

        if (0 == $result) {
            $result = $r2->getStagingScore() <=> $r1->getStagingScore();

            if (0 == $result) {
                $result = $r2->getRandomNumber() <=> $r1->getRandomNumber();
            }
        }

        return $result;
    }
}
