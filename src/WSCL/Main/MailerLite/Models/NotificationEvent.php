<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Models;

class NotificationEvent
{
    public int $accountId;
    public NotificationData $data;
    public int $timestamp;
    public string $type;
    public int $webhookId;
}
