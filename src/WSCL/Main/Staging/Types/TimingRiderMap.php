<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\TimingRcd;
use WSCL\Main\Staging\Entity\PersonInfoIntf;

/**
 * Implements a mapping of registration system id to timing system record.
 */
class TimingRiderMap
{
    /** @var TimingRcd[] */
    private array $riderMap = array();

    public function put(TimingRcd $timingRcd): void
    {
        // Get the current entry from the map (or null)
        $currEntry = $this->get($timingRcd);

        // If there is an entry, update last season points
        if (isset($currEntry)) {
            $currEntry->setLastSeasonPoints($timingRcd->getSeasonPoints());
        } else {
            // Otherwise add to the map
            $this->riderMap[$timingRcd->getId()] = $timingRcd;
        }
    }

    public function get(PersonInfoIntf $info): ?TimingRcd
    {
        $rcd = null;
        $id = $info->getId();

        if (isset($id)) {
            $rcd = $this->riderMap[$id] ?? null;
        }

        if (!isset($rcd)) {
            $filtered = array_filter(
                $this->riderMap,
                fn($rcd) =>
                    0 == strcasecmp($info->getGender(), $rcd->getGender()) &&
                    0 == strcasecmp($info->getFirstName(), $rcd->getFirstName()) &&
                    0 == strcasecmp($info->getLastName(), $rcd->getLastName()) &&
                    $info->getBirthDate() == $rcd->getBirthDate()
                );

            if (1 == count($filtered)) {
                $rcd = array_shift($filtered);
            }
        }

        return $rcd;
    }

    public function size(): int
    {
        return count($this->riderMap);
    }
}
