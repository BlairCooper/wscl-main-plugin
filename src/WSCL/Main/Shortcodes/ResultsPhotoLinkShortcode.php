<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;

class ResultsPhotoLinkShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName(): string
    {
        return 'wscl-results-photo-link';
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
            'src' => ''
        ), $attrs);

        $label = 'Photos';

        if (!empty($attrs['src']) ) {
            $label = $label . ' by ' . $attrs['src'];
        }

        return do_shortcode(
            sprintf(
                '[av_button label="%s" link="manually,%s" link_target="_blank" size="small" position="center" custom_class="wsclResultsButton" color="theme-color"]',
                $label,
                $attrs['url']
                )
            );
    }
}
