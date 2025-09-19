<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\RegistrationRcd;


/**
 * Implements a mapping of registration systsm id to registration record.
 */
class RegisteredRiderMap
{
    /** @var RegistrationRcd[] */
    private array $riderMap = array();

    public function put(int $regSysId, RegistrationRcd $regRcd): void
    {
        $this->riderMap[$regSysId] = $regRcd;
    }

    /**
     * Fetch the array of riders
     *
     * @return RegistrationRcd[]
     */
    public function values(): array
    {
        return array_values($this->riderMap);
    }
}
