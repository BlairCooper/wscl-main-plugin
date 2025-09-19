<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;

abstract class RiderBySet
{
    /** @var Rider[] */
    protected array $entries = array();

    public function add(Rider $rider): void
    {
        array_push($this->entries, $rider);
    }

    /**
     *
     * @return Rider[] Array of riders sort by name
     */
    public function getEntries(): array
    {
        usort($this->entries, array(static::class, 'compare'));

        return $this->entries;
    }

    abstract protected static function compare(Rider $r1, Rider $r2): int;
}
