<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Enums;

enum MembershipReportStatus : string {
    case CANCELLED = 'Cancelled';
    case HOLD = 'Hold';
    case INCOMPLETE = 'Incomplete';
    case INACTIVE_HIGHER_LEVEL = 'Inactive - Higher Level Achieved';
    case ISSUED = 'Issued';
    case PENDING_REQUIREMENTS = 'Pending Requirements';
    case PROCESSING = 'Processing';
}
