<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use RCS\Json\JsonEntity;

class ReportStartResponse extends JsonEntity
{
    public string $taskUuid;
}
