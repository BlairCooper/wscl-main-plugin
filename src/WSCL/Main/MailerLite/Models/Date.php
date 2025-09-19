<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Models;

class Date
{
    public \DateTime $date;
    public int $timezoneType;
    public string $timezone;
}
