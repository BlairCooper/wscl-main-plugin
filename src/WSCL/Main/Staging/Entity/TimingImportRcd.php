<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use RCS\Csv\CsvBindByNameTrait;
use WSCL\Main\Staging\IValidateRider;

/**
 * Base class for data being imported from the timing system.
 */
abstract class TimingImportRcd implements TimingRcd, IValidateRider
{
    use CsvBindByNameTrait;
}
