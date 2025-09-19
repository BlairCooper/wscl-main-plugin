<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;

class TeamSizeEntry
{
    private string $teamName;
    private int $msSize = 0;
    private int $hsSize = 0;

    public function __construct(string $teamName)
    {
        $this->teamName = $teamName;
    }

    public function addRider(string $category): void
    {
        if (Rider::isHighSchool($category)) {
            $this->hsSize++;
        } else {
            $this->msSize++;
        }
    }

    public function getTeamName(): string
    {
        return $this->teamName;
    }

    public function getHighSchoolSize(): int
    {
        return $this->hsSize;
    }

    public function getMiddleSchoolSize(): int
    {
        return $this->msSize;
    }
}
