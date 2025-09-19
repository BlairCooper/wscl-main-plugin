<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging;

use RCS\Traits\SingletonTrait;

class StagingHelper
{
    // Roles/Capabilities
    const STAGING_API_ROLE = 'wscl_staging_api_role';
    const CAP_EXECUTE_API   = 'wscl_staging_execute_cap';

    const CUSTOM_POST_TYPE = 'wscl-staging';

    use SingletonTrait;

    protected function initializeInstance(): void
    {
        add_action('init', [$this, 'registerPostType']);

        if (defined('DEV_ENV')) {
            add_filter('rest_authentication_errors', '__return_true', 200);
        }
    }

    public function registerPostType(): void
    {
        $postTypeArgs = (object) [
            'labels' => array(
                'name' => __('WSCL Staging Event')
            ),
            // 'public' => false,   // default is false
            'has_archive' => false,
            'rewrite' => false,
            //'show_ui' => true,    // defaults to $public
            //'show_in_menu' => true, // defaults to $show_ui
            'capability_type' => 'wscl-staging',
            'map_meta_cap' => false,
            'capabilities' => array()
        ];

        register_post_type(self::CUSTOM_POST_TYPE, wp_parse_args($postTypeArgs));
    }
}
