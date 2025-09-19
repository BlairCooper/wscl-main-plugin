<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\RacePlateRcd;

class RiderByPlateSet
{
    /** @var RacePlateRcd[] */
    private array $entries = array();

    public function add(RacePlateRcd $rider): void
    {
        array_push($this->entries, $rider);
    }

    /**
     *
     * @return RacePlateRcd[] Array of riders sort by name
     */
    public function getEntries(): array
    {
        usort($this->entries, array($this, 'compare'));

        return $this->entries;
    }

    private function compare(RacePlateRcd $r1, RacePlateRcd $r2): int
    {
        return $r1->bib <=> $r2->bib;
    }
}
