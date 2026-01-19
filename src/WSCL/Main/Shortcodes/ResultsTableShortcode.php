<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;

class ResultsTableShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName(): string
    {
        return 'wscl-results-table';
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs= [], $content = ''): string
    {
        return '<div class="wsclResultsTable">' . do_shortcode( shortcode_unautop( $content ) ) . '</div>';
    }
}
