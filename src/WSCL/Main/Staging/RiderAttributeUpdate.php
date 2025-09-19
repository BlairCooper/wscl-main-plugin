<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging;

use RCS\WP\BgProcess\BgProcess;

interface RiderAttributeUpdate
{
    public function setRacePlateName(string $name): void;
    public function setRaceGender(string $gender): void;
    public function setRaceCategory(string $category): void;

    public function commit(BgProcess $bgProcess): void;
}
