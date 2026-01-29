<?php
declare(strict_types = 1);
namespace WSCL\Main;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RCS\Cache\DataCache;
use RCS\WP\PluginInfo;
use RCS\WP\PluginInfoInterface;
use RCS\WP\PluginLogger;
use RCS\WP\PluginOptionsInterface;
use RCS\WP\ShortcodeProxy;
use RCS\WP\BgProcess\BgProcess;
use RCS\WP\BgProcess\BgProcessInterface;
use RCS\WP\Database\DatabaseUpdater;
use RCS\WP\Database\DatabaseUpdatesInterface;
use WSCL\Main\CcnBikes\CcnBikesOptionsInterface;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\MailerLite\MailerLiteClient;
use WSCL\Main\MailerLite\MailerLiteOptionsInterface;
use WSCL\Main\MailerLite\Cron\MailerLiteBgProcess;
use WSCL\Main\MailerLite\Cron\MailerLiteCronJob;
use WSCL\Main\Maps\TeamMapShortcode;
use WSCL\Main\Maps\VenueMapShortcode;
use WSCL\Main\Petitions\PetitionsHelper;
use WSCL\Main\RaceResult\RaceResultClient;
use WSCL\Main\Scholarships\ScholarshipOptionsInterface;
use WSCL\Main\Scholarships\ScholarshipOptionsTab;
use WSCL\Main\Scholarships\ScholarshipsHelper;
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
use WSCL\Main\Staging\StagingOptionsTab;
use WSCL\Main\Staging\Controllers\AnalyzeEventController;
use WSCL\Main\Staging\Controllers\CategoryController;
use WSCL\Main\Staging\Controllers\EventController;
use WSCL\Main\Staging\Controllers\NameMapController;
use WSCL\Main\Staging\Controllers\RaceController;
use WSCL\Main\Staging\Controllers\RaceResultController;
use WSCL\Main\Staging\Controllers\StagingController;

class ServiceConfig
{
    public const PLUGIN_ENTRYPOINT = 'plugin.entryPoint';
    public const SETTINGS_TABS = 'settings.tabs';
    public const STAGING_REST_CONTROLLERS = 'staging.rest.controllers';

    /**
     *
     * @return array<string, mixed>
     */
    public static function getDefinitions(): array
    {
        return [
            PluginInfoInterface::class => \DI\create(PluginInfo::class)
                ->constructor(\DI\get(ServiceConfig::PLUGIN_ENTRYPOINT)),

            LoggerInterface::class => \DI\autowire(PluginLogger::class),

            WsclMainOptionsInterface::class => \DI\factory([WsclMainOptions::class, 'init']),
            PluginOptionsInterface::class => \DI\get(WsclMainOptionsInterface::class),
            MailerLiteOptionsInterface::class => \DI\get(WsclMainOptionsInterface::class),
            ScholarshipOptionsInterface::class => \DI\get(WsclMainOptionsInterface::class),
            CcnBikesOptionsInterface::class => \DI\get(WsclMainOptionsInterface::class),

            ScholarshipsHelper::class => \DI\autowire(),

            BgProcessInterface::class => \DI\autowire(BgProcess::class),
            MailerLiteBgProcess::class => \DI\autowire(),

            self::SETTINGS_TABS => [
                \DI\autowire(GeneralOptionsTab::class),
                \DI\autowire(StagingOptionsTab::class),
                \DI\autowire(ScholarshipOptionsTab::class)
            ],

            /**
             * Shortcodes
             */
            ShortcodeProxy::class => \DI\autowire(),

            DirectorEmailAliasShortcode::class => \DI\autowire(),
            DirectorFirstNameShortcode::class => \DI\autowire(),
            DirectorFullNameShortcode::class => \DI\autowire(),
            InsertJotFormShortcode::class => \DI\autowire(),
            PetitionApprovalShortcode::class => \DI\autowire(),
            PetitionInfoTableShortcode::class => \DI\autowire(),
            PostExcerptShortcode::class => \DI\autowire(),
            PostTitleShortcode::class => \DI\autowire(),
            RaceParticipantsCategoriesShortcode::class => \DI\autowire(),
            RaceResultRegistrationShortcode::class => \DI\autowire(),
            ResultsDetailsRaceDateShortcode::class => \DI\autowire(),
            ResultsDetailsRaceNameShortcode::class => \DI\autowire(),
            ResultsDetailsRaceNumberShortcode::class => \DI\autowire(),
            ResultsDetailsRaceResultsShortcode::class => \DI\autowire(),
            ResultsDetailsRaceTitleShortcode::class => \DI\autowire(),
            ResultsEntryShortcode::class => \DI\autowire(),
            ResultsLinkButtonShortcode::class => \DI\autowire(),
            ResultsPhotoLinkShortcode::class => \DI\autowire(),
            ResultsRaceShortcode::class => \DI\autowire(),
            ResultsTableShortcode::class => \DI\autowire(),

            TeamMapShortcode::class => \DI\autowire(),
            VenueMapShortcode::class => \DI\autowire(),

            ProgramBalanceShortcode::class => \DI\autowire(),
            ProgramFeeShortcode::class => \DI\autowire(),
            ProgramNameShortcode::class => \DI\autowire(),

            self::STAGING_REST_CONTROLLERS => [
                \DI\autowire(EventController::class)
                    ->constructor(apiVersion: 1, apiRoute: '/staging'),
                \DI\autowire(CategoryController::class)
                    ->constructor(apiVersion: 1, apiRoute: '/staging'),
                \DI\autowire(RaceController::class)
                    ->constructor(apiVersion: 1, apiRoute: '/staging'),
                \DI\autowire(StagingController::class)
                    ->constructor(apiVersion: 1, apiRoute: '/staging', rrClient: \DI\get(RaceResultClient::class)),
                \DI\autowire(NameMapController::class)
                    ->constructor(apiVersion: 1, apiRoute: '/staging'),
                \DI\autowire(RaceResultController::class)
                    ->constructor(apiVersion: 1, apiRoute: '/staging', rrClient: \DI\get(RaceResultClient::class)),
                \DI\autowire(AnalyzeEventController::class)
                    ->constructor(apiVersion: 1, apiRoute: '/staging', rrClient: \DI\get(RaceResultClient::class)),
                ],

            WsclMainAdminSettings::class => \DI\autowire()
                ->constructor(tabs: \DI\get(self::SETTINGS_TABS)),

//             MailerLiteHelper::class => function (ContainerInterface $container) {
//                 return MailerLiteHelper::init(
//                     $container->get(LoggerInterface::class)
//                 );
//             },

            DatabaseUpdatesInterface::class => \DI\autowire(DatabaseUpdates::class),
            DatabaseUpdater::class => \DI\autowire(),

            PetitionsHelper::class => \DI\autowire(),

            CacheInterface::class => \DI\autowire(DataCache::class),

            CcnClient::class => \DI\autowire(),
            MailerLiteClient::class => \DI\autowire(),
            RaceResultClient::class => \DI\autowire(),

            MailerLiteCronJob::class => \DI\autowire(MailerLiteCronJob::class),
            ScholarshipsCronJob::class => \DI\autowire(ScholarshipsCronJob::class),
        ];
    }
}
