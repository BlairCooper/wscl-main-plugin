<?php
declare(strict_types=1);
namespace WSCL\Main;

/**
 * Entry point of WSCL Main website customizations.
 *
 * @wordpress-plugin
 * Plugin Name:         WSCL Main site Customizations
 * Description:         Plugin to support customizations for the washingtonleague.org website.
 * Version:             2.0.9
 * Requires at least:   6.8.0
 * Requires PHP:        8.2
 * Author:              Blair Cooper
 * Requires Plugins:
 */

// If this file is called directly, abort.
defined('ABSPATH') || exit;

const VENDOR_AUTOLOAD_PHP = '/vendor/autoload.php';

if (file_exists(__DIR__.VENDOR_AUTOLOAD_PHP)) {
    $vendorAutoload = __DIR__.VENDOR_AUTOLOAD_PHP;
} else {
    $vendorAutoload = __DIR__.'/..'.VENDOR_AUTOLOAD_PHP;
}

require_once($vendorAutoload);  // NOSONAR

(new WsclMainPlugin())->init(__FILE__);
