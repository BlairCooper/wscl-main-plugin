<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes;

use RCS\WP\PluginOptionsInterface;

interface CcnBikesOptionsInterface extends PluginOptionsInterface
{
    public function getCcnRestApiUrl(): ?string;
    public function getCcnUsername(): ?string;
    public function getCcnPassword(): ?string;
}
