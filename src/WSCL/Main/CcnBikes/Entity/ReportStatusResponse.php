<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use RCS\Json\JsonEntity;
use WSCL\Main\CcnBikes\Enums\ReportStateEnum;

class ReportStatusResponse extends JsonEntity
{
    public int $id;
    public string $taskId;

    public ReportStateEnum $state;
}
