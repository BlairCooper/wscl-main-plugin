<?php
declare(strict_types = 1);
namespace WSCL\Main;

use RCS\WP\PluginOptionsInterface;

interface WsclMainOptionsInterface extends PluginOptionsInterface
{
    public function getSiteEmailName(): string;
    public function getSiteEmailAddress(): string;

    public static function isMailerLitePluginInstalled(): bool;

    public function getGoogleMapsApiKey(): ?string;

    public function getPdfServiceUrl(): ?string;

    public function getRaceResultAccount(): ?int;
    public function getRaceResultUsername(): ?string;
    public function getRaceResultPassword(): ?string;

    public function getDirectorFirstName(): string;
    public function getDirectorFullName(): string;
    public function getDirectorEmailAlias(): string;
    public function getDeveloperEmailAddress(): string;
}
