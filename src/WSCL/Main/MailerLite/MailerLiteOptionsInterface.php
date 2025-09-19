<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite;

use RCS\WP\PluginOptionsInterface;

interface MailerLiteOptionsInterface extends PluginOptionsInterface
{
    public function getMailerLiteApiKey(): ?string;
    public function getDeveloperEmailAddress(): string;
}
