<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use RCS\Csv\CsvBindByNameTrait;
use WSCL\Main\Staging\IValidateRider;

/**
 * Base class for data being imported from the registration system.
 */
abstract class RegistrationImportRcd implements RegistrationRcd, IValidateRider
{
    use CsvBindByNameTrait;

    public static function formatPhoneNumber(string $inPhone): string
    {
        $result = $inPhone;

        $matches = array();

        if (preg_match("/\(?(\d{3})\]?-?(\d{3})-?(\d{4})/", $inPhone, $matches)) {
            $result = sprintf("%s-%s-%s", $matches[1], $matches[2], $matches[3]);
        }

        return $result;
    }

}
