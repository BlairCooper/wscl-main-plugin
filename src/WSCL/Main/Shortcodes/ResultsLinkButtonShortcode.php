<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Shortcodes\ShortcodeBase;

class ResultsLinkButtonShortcode extends ShortcodeBase
{
    public function __construct(PluginInfoInterface $pluginInfo)
    {
        parent::__construct($pluginInfo, 'wscl-results-link-button');
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
