<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Models;

class WebhooksResponse
{
    /** @var WebhookDetails[] */
    public array $webhooks;
    public int $count;
    public int $start;
    public int $limit;
}
