<?php declare(strict_types = 1);
namespace WSCL\Main\Scholarships\Shortcodes;

use WSCL\Main\Scholarships\ScholarshipOptionsInterface;
use WSCL\Main\Scholarships\ScholarshipsHelper;

class ProgramNameShortcode extends ShortcodeBase
{
    public function __construct(
        ScholarshipOptionsInterface $options
        )
    {
        parent::__construct($options);
    }

    public static function getTagName(): string
    {
        return 'wscl_fa_program_name';
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
