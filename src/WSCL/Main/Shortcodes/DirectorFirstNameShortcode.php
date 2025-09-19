<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Shortcodes\ShortcodeBase;
use WSCL\Main\WsclMainOptionsInterface;

class DirectorFirstNameShortcode extends ShortcodeBase
{
    public function __construct(
        PluginInfoInterface $pluginInfo,
        private WsclMainOptionsInterface $options
        )
    {
        parent::__construct($pluginInfo, 'wscl-ed-firstname');
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        return $this->options->getDirectorFirstName();
    }
}
