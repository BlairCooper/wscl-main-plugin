<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Enums;

enum IdentityAttributeType : string {
    case DateTime = 'DateTime';
    case ShortText = 'ShortText';
    case LongText = 'LongText';
    case SingleOption = 'SingleOption';
    case MultiOption = 'MultiOption';
    case File = 'File';
}
