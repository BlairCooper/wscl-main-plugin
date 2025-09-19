<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Enums;

enum ListingStatusEnum : string {
    case DRAFT = 'DR';
    case APPROVED = 'AP';
    case DEACTIVATED = 'DC';
}
