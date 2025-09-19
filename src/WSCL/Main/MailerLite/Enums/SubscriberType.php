<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Enums;

enum SubscriberType : string {
    case ACTIVE = 'active';
    case UNSUBSCRIBED = 'unsubscribed';
    case BOUNCED = 'bounced';
    case JUNK = 'junk';
    case UNCONFIRMED = 'unconfirmed';
}
