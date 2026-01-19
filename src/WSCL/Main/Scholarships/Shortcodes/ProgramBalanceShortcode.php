<?php declare(strict_types = 1);
namespace WSCL\Main\Scholarships\Shortcodes;

use WSCL\Main\Scholarships\ScholarshipOptionsInterface;

class ProgramBalanceShortcode extends ShortcodeBase
{
    public function __construct(
        ScholarshipOptionsInterface $options
        )
    {
        parent::__construct($options);
    }

    public static function getTagName(): string
    {
        return 'wscl_fa_program_balance';
    }

    protected function privRenderShortcode(array $metas): string
    {
        return strval($this->getCost() - $this->getAward());
    }
}
