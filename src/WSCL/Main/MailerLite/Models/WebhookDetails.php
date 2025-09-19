<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Models;

class WebhookDetails
{
    public int $id;
    public string $event;
    public string $url;
    public Date $createdAt;
    public Date $updatedAt;
}
