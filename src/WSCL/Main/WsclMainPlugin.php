<?php
declare(strict_types = 1);
namespace WSCL\Main;

use DI\ContainerBuilder;
use RCS\Logging\ErrorLogInterceptor;
use RCS\WP\PluginInfo;
use RCS\WP\Database\DatabaseUpdater;
use WSCL\Main\MailerLite\Cron\MailerLiteCronJob;
use WSCL\Main\Scholarships\Cron\ScholarshipsCronJob;
use WSCL\Main\Scholarships\Shortcodes\ProgramBalanceShortcode;
use WSCL\Main\Scholarships\Shortcodes\ProgramFeeShortcode;
use WSCL\Main\Scholarships\Shortcodes\ProgramNameShortcode;
use WSCL\Main\Shortcodes\DirectorEmailAliasShortcode;
use WSCL\Main\Shortcodes\DirectorFirstNameShortcode;
use WSCL\Main\Shortcodes\DirectorFullNameShortcode;
use WSCL\Main\Shortcodes\InsertJotFormShortcode;
use WSCL\Main\Shortcodes\PetitionApprovalShortcode;
use WSCL\Main\Shortcodes\PetitionInfoTableShortcode;
use WSCL\Main\Shortcodes\PostExcerptShortcode;
use WSCL\Main\Shortcodes\PostTitleShortcode;
use WSCL\Main\Shortcodes\RaceParticipantsCategoriesShortcode;
use WSCL\Main\Shortcodes\RaceResultRegistrationShortcode;
use WSCL\Main\Shortcodes\ResultsDetailsRaceDateShortcode;
use WSCL\Main\Shortcodes\ResultsDetailsRaceNameShortcode;
use WSCL\Main\Shortcodes\ResultsDetailsRaceNumberShortcode;
use WSCL\Main\Shortcodes\ResultsDetailsRaceResultsShortcode;
use WSCL\Main\Shortcodes\ResultsDetailsRaceTitleShortcode;
use WSCL\Main\Shortcodes\ResultsEntryShortcode;
use WSCL\Main\Shortcodes\ResultsLinkButtonShortcode;
use WSCL\Main\Shortcodes\ResultsPhotoLinkShortcode;
use WSCL\Main\Shortcodes\ResultsRaceShortcode;
use WSCL\Main\Shortcodes\ResultsTableShortcode;
use WSCL\Main\Petitions\PetitionsHelper;
use RCS\WP\ShortcodeProxy;
use WSCL\Main\Maps\TeamMapShortcode;
use WSCL\Main\Maps\VenueMapShortcode;
use WSCL\Main\Scholarships\ScholarshipsHelper;

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
    }

    private static function getCompiledContainerPath(string $entryPointFile): string
    {
        return (new PluginInfo($entryPointFile))->getPath();
    }

    private function initializeContainer(string $entryPointFile): void
    {
        if (!function_exists('get_home_path')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(ServiceConfig::getDefinitions());

        if (!file_exists(get_home_path() . 'wp-config-local.php')) {
            $containerBuilder->enableCompilation(self::getCompiledContainerPath($entryPointFile));
        }

        $container = $containerBuilder->build();

        $container->set(ServiceConfig::PLUGIN_ENTRYPOINT, $entryPointFile);

        new ErrorLogInterceptor([                               // NOSONAR - not useless instantiation
            E_USER_NOTICE => ['_load_textdomain_just_in_time']
            ]
        );

        /** @var DatabaseUpdater */
        $dbUpdater = $container->get(DatabaseUpdater::class);
        $dbUpdater->privUpgradeDatabase();

        $container->get(ScholarshipsHelper::class);

        $container->get(MailerLiteCronJob::class);
        $container->get(ScholarshipsCronJob::class);

        $container->get(ServiceConfig::STAGING_REST_CONTROLLERS);

        $container->get(PetitionsHelper::class);

        if (is_admin()) {
            $container->get(WsclMainAdminSettings::class);
        }

        $this->options = $container->get(WsclMainOptionsInterface::class);

        /** @var ShortcodeProxy $scProxy */
        $scProxy = $container->get(ShortcodeProxy::class);
        $scProxy->addShortcodes([
            DirectorEmailAliasShortcode::class,
            DirectorFirstNameShortcode::class,
            DirectorFullNameShortcode::class,
            InsertJotFormShortcode::class,
            PetitionApprovalShortcode::class,
            PetitionInfoTableShortcode::class,
            PostExcerptShortcode::class,
            PostTitleShortcode::class,
            RaceParticipantsCategoriesShortcode::class,
            RaceResultRegistrationShortcode::class,
            ResultsDetailsRaceDateShortcode::class,
            ResultsDetailsRaceNameShortcode::class,
            ResultsDetailsRaceNumberShortcode::class,
            ResultsDetailsRaceResultsShortcode::class,
            ResultsDetailsRaceTitleShortcode::class,
            ResultsEntryShortcode::class,
            ResultsLinkButtonShortcode::class,
            ResultsPhotoLinkShortcode::class,
            ResultsRaceShortcode::class,
            ResultsTableShortcode::class,

            TeamMapShortcode::class,
            VenueMapShortcode::class,

            ProgramBalanceShortcode::class,
            ProgramFeeShortcode::class,
            ProgramNameShortcode::class
            ]
        );
    }
}
