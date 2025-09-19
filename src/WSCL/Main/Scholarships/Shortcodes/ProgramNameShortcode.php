<?php declare(strict_types = 1);
namespace WSCL\Main\Scholarships\Shortcodes;

use RCS\WP\PluginInfoInterface;
use WSCL\Main\Scholarships\ScholarshipOptionsInterface;
use WSCL\Main\Scholarships\ScholarshipsHelper;

class ProgramNameShortcode extends ShortcodeBase
{
    public function __construct(
        PluginInfoInterface $pluginInfo,
        ScholarshipOptionsInterface $options
        )
    {
        parent::__construct($pluginInfo, 'wscl_fa_program_name', $options);
    }

    protected function privRenderShortcode(array $metas): string
    {
        $result = '';

        if ($this->isForLeagueFee()) {
            $result = ScholarshipsHelper::getSeasonPrefix() . ' Season';
        } else {
            $result = $this->getWhatFor();
        }

        return $result;
    }
}
