<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging;

use Psr\Log\LoggerInterface;

interface IValidateRider
{
    /**
     * Validate whether the instance has all of the necessary registration
     * data.
     *
     * @return bool Returns true if the the data is present and valid, otherwise
     *      returns false.
     */
    public function validateRider(LoggerInterface $logger, RiderAttributeUpdate $attrUpdate): bool;

    /**
     * Check if missing data was identified while validating the rider.
     *
     * @return bool Returns true if data was missing, otherwise returns false.
     */
    public function isMissingData(): bool;

}
