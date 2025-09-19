<?php
declare(strict_types = 1);
namespace WSCL\Main\Scholarships;

use RCS\WP\PluginOptionsInterface;

interface ScholarshipOptionsInterface extends PluginOptionsInterface
{
    public function getFallSeasonFee(): ?int;
    public function getFallSeasonMinimum(): ?int;
    public function getSpringSeasonFee(): ?int;
    public function getSpringSeasonMinimum(): ?int;
    public function getMinimumScore(): ?int;
    public function getCoachFee(): ?int;
}
