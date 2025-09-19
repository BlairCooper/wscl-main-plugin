<?php
declare(strict_types = 1);
namespace WSCL\Main;

use DI\ContainerBuilder;
use RCS\Logging\ErrorLogInterceptor;
use RCS\WP\Database\DatabaseUpdater;
use WSCL\Main\MailerLite\Cron\MailerLiteCronJob;
use WSCL\Main\Scholarships\Cron\ScholarshipsCronJob;

class WsclMainPlugin
{
    private WsclMainOptionsInterface $options;

    public function init(string $entryPointFile): void
    {
        // Enable conditional menus - do this immediately as using a hook will be too late
        add_theme_support('avia_conditionals_for_mega_menu');

        add_action(
            'init',
            function () use ($entryPointFile) {
                $this->initializeContainer($entryPointFile);

                wp_register_script('wscl_js_urls', '');
                wp_enqueue_script('wscl_js_urls');
                wp_add_inline_script(
                    'wscl_js_urls',
                    'const wscl_ajax_url="'.admin_url('admin-ajax.php').'"; const wscl_json_url="'.get_home_url().'/wp-json/";'
                    );
            }
            );

        add_filter(
            'wp_mail_from',
            function (string $fromEmail) {
                return $this->options->getSiteEmailAddress();
            }
            );

        add_filter(
            'wp_mail_from_name',
            function (string $fromName) {
                return $this->options->getSiteEmailName();
            }
            );

        add_filter('wp_is_application_passwords_available', '__return_true');

        add_action(
            'http_api_curl',
            function ($handle, array $parsed_args, string $url) {
                curl_setopt($handle, CURLOPT_VERBOSE, true);
                // curl_setopt($handle, CURLOPT_STDERR, 1);
            },
            10,
            3
            );

        add_action(
            'upgrader_process_complete',
            function (object $upgrader_object, array $options) use ($entryPointFile) {
                if ($options['action'] == 'update' &&
                    $options['type'] == 'plugin' &&
                    isset( $options['plugins'] ) &&
                    in_array( plugin_basename( $entryPointFile ), $options['plugins']))
                {
                    $path = self::getCompiledContainerPath();

                    if (file_exists($path)) {
                        array_map('unlink', glob("$path/*.php"));
                    }
                }
            },
            10,
            2
            );
    }

    public static function getCompiledContainerPath(): string
    {
        $reflectionClass = new \ReflectionClass(self::class);
        $shortName = $reflectionClass->getShortName();

        return \wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'CompiledContainers' . DIRECTORY_SEPARATOR . $shortName;
    }

    private function initializeContainer(string $entryPointFile): void
    {
        if (!function_exists('get_home_path')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';    // @phpstan-ignore requireOnce.fileNotFound
        }

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(ServiceConfig::getDefinitions());

        if (!file_exists(get_home_path() . 'wp-config-local.php')) {
            $containerBuilder->enableCompilation(self::getCompiledContainerPath());
        }

        $container = $containerBuilder->build();

        $container->set(ServiceConfig::PLUGIN_ENTRYPOINT, $entryPointFile);

        ErrorLogInterceptor::init([
            E_USER_NOTICE => ['_load_textdomain_just_in_time']
        ]
            );

        /** @var DatabaseUpdater */
        $dbUpdater = $container->get(DatabaseUpdater::class);
        $dbUpdater->privUpgradeDatabase();

        $container->get(MailerLiteCronJob::class);
        $container->get(ScholarshipsCronJob::class);

        $container->get(ServiceConfig::SHORTCODES);
        $container->get(ServiceConfig::STAGING_REST_CONTROLLERS);

        if (is_admin()) {
            $container->get(WsclMainAdminSettings::class);
        }

        $this->options = $container->get(WsclMainOptionsInterface::class);
    }
}
