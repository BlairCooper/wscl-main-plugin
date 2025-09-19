<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;

class RiderByLastFirstNameSet extends RiderBySet
{
    protected static function compare(Rider $r1, Rider $r2): int
    {
        $result = $r1->getLastName() <=> $r2->getLastName();

        if (0 == $result) {
            $result = $r1->getFirstName() <=> $r2->getFirstName();

            if (0 == $result) {
                $result = $r1->getRegSysId() <=> $r2->getRegSysId();
            }
        }

        return $result;
    }
}
