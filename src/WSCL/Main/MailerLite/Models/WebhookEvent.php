<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Models;

class WebhookEvent
{
    public string $url;
    public string $event;

    public function __construct(string $url = null, string $event = null) {
        if (isset($url)) {
            $this->url = $url;
        }

        if (isset($event)) {
            $this->event = $event;
        }
    }
}
