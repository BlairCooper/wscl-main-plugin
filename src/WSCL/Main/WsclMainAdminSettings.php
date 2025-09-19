<?php
declare(strict_types = 1);
namespace WSCL\Main;

use RCS\WP\Settings\AdminSettings;
use RCS\WP\Settings\AdminSettingsTab;
use Psr\Log\LoggerInterface;
use RCS\WP\PluginInfoInterface;


class WsclMainAdminSettings extends AdminSettings
{
    const OPTIONS_PAGE_TITLE = 'WSCL Customization Settings';
    const OPTIONS_MENU_TITLE = 'WSCL Customizations';
    const OPTIONS_PAGE_SLUG  = 'WSCLSiteOptions';

    /**
     * Initialize the class and set its properties.
     *
     * @param PluginInfoInterface $pluginInfo
     * @param LoggerInterface $logger
     * @param AdminSettingsTab[] $tabs
     */
    public function __construct(
        PluginInfoInterface $pluginInfo,
        LoggerInterface $logger,
        array $tabs
        ) {
        parent::__construct(
            $pluginInfo,
            $tabs,
            self::OPTIONS_PAGE_TITLE,
            self::OPTIONS_PAGE_SLUG,
            self::OPTIONS_MENU_TITLE,
            $logger
            );

        $this->initializeInstance();
    }
}
