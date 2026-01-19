<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;

class ResultsDetailsRaceDateShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName(): string
    {
        return 'wscl-results-details-race-date';
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        return $_POST['__raceDate'] ?? 'Missing race date';
    }
}
