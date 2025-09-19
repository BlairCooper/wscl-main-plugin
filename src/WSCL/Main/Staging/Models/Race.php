<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Models;

use RCS\Json\JsonDateTime;

class Race
{
    private const RACE_TIME_FORMAT = 'g:i A';

    public int $id = -1;
    public string $name = '';
    public JsonDateTime $startTime;
    public JsonDateTime $stagingTime;
    public string $timezone = 'America/Los_Angeles';
    /** @var int[] */
    public array $categoryIds = array();

    private int $riderCnt;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * @return int[]
     */
    public function getCategories(): array
    {
        return $this->categoryIds;
    }

    /**
     *
     * @param int[] $ids
     */
    public function setCategories(array $ids): void
    {
        $this->categoryIds = $ids;
    }

    public function hasCategory(int $id): bool
    {
        return in_array($id, $this->categoryIds);
    }

    public function update(Race $newRace): void
    {
        $this->name = $newRace->getName();
        $this->startTime = $newRace->startTime;
        $this->stagingTime = $newRace->stagingTime;
        $this->timezone = $newRace->timezone;
        $this->categoryIds = $newRace->categoryIds;
    }

    public function removeCategory(int $catId): void
    {
        $this->removeCategories(array($catId));
    }

    /**
     *
     * @param int[] $catIds
     */
    public function removeCategories(array $catIds): void
    {
        $this->categoryIds = array_diff($this->categoryIds, $catIds);
    }

    public function setRiderCnt(int $raceCnt): void
    {
        $this->riderCnt = $raceCnt;
    }

    public function getRiderCnt(): int
    {
        return $this->riderCnt;
    }

    public function getStartTime(): \DateTime
    {
        return $this->startTime->setTimezone(new \DateTimeZone($this->timezone));
    }

    public function getStartTimeAsString(): string
    {
        return $this->getStartTime()->format(self::RACE_TIME_FORMAT);
    }

    public function getStagingTime(): \DateTime
    {
        return $this->stagingTime->setTimezone(new \DateTimeZone($this->timezone));
    }

    public function getStagingTimeAsString(): string
    {
        return $this->getStagingTime()->format(self::RACE_TIME_FORMAT);
    }

    /**
     * Synchronize the start and staging dates with the event date.
     *
     * @param \DateTime $eventDate The date of the event.
     */
    public function syncWithEventDate(\DateTime $eventDate): void
    {
        $this->stagingTime->setDate(
            intval($eventDate->format('Y')),
            intval($eventDate->format('m')),
            intval($eventDate->format('d'))
            );
        $this->startTime->setDate(
            intval($eventDate->format('Y')),
            intval($eventDate->format('m')),
            intval($eventDate->format('d'))
            );
    }
}
