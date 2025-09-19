<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Enums;

enum ReportStateEnum : string {
    case STARTED = 'STARTED';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';
    case FAILURE = 'FAILURE';
}
