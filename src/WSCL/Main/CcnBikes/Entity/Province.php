<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class Province
{
    public int $id;
    public string $abbrev;

    public function getAbbreviation(): string
    {
        return $this->abbrev;
    }
}
