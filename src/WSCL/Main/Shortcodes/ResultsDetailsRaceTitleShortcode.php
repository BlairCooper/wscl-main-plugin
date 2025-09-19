<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Shortcodes\ShortcodeBase;

class ResultsDetailsRaceTitleShortcode extends ShortcodeBase
{
    public function __construct(PluginInfoInterface $pluginInfo)
    {
        parent::__construct($pluginInfo, 'wscl-results-details-race-title');
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs= [], $content = ''): string
    {
        return $_POST['__raceTitle'] ?? 'Missing race title';
    }
}
