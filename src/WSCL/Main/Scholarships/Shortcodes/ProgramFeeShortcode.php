<?php declare(strict_types = 1);
namespace WSCL\Main\Scholarships\Shortcodes;

use RCS\WP\PluginInfoInterface;
use WSCL\Main\Scholarships\ScholarshipOptionsInterface;

class ProgramFeeShortcode extends ShortcodeBase
{
    public function __construct(
        PluginInfoInterface $pluginInfo,
        ScholarshipOptionsInterface $options)
    {
        parent::__construct($pluginInfo, 'wscl_fa_program_fee', $options);
    }

    protected function privRenderShortcode(array $metas): string
    {
        $fee = $this->getCost();

        return 0 == $fee ? '' : strval($fee);
    }
}
