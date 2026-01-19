<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;

class ResultsLinkButtonShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName(): string
    {
        return 'wscl-results-link-button';
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs= [], $content = ''): string
    {
        $attrs = shortcode_atts(array(
            'url' => null,
            'label' => '',
            'color' => 'theme-color'
        ), $attrs);

        return do_shortcode(sprintf('[av_button label="%s" link="manually,%s" link_target="_blank" size="small" position="center" custom_class="wsclResultsButton" color="%s"]', $attrs['label'], $attrs['url'], $attrs['color']));
    }
}
