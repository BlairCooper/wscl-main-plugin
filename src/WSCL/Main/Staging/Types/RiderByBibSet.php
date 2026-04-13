<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;

class RiderByBibSet extends RiderBySet
{
    protected static function compare(Rider $r1, Rider $r2): int
    {
        return $r1->getBibNumber() <=> $r2->getBibNumber();
    }
}
