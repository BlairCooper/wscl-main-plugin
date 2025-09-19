<?php
declare(strict_types = 1);
namespace WSCL\Main;

use RCS\WP\Settings\AdminSettingsTab;
use RCS\WP\Validation\StringValidator;
use WSCL\Main\MailerLite\MailerLiteClient;
use Psr\Log\LoggerInterface;
use RCS\WP\PluginOptionsInterface;

class GeneralOptionsTab extends AdminSettingsTab
{
    private const TAB_NAME = 'General';

    const EMAIL_SECTION_SETTINGS_ID = 'emailSection';
    const EMAIL_SECTION_SETTINGS_TITLE = 'Site Email Settings';

    const DIRECTOR_SECTION_SETTINGS_ID = 'directorSection';
    const DIRECTOR_SECTION_SETTINGS_TITLE = 'Executive Director Settings';

    const MAILERLITE_SECTION_SETTINGS_ID = 'mailerliteSection';
    const MAILERLITE_SECTION_SETTINGS_TITLE = 'MailerLite Settings';

    const GOOGLE_SECTION_SETTINGS_ID = 'googleSection';
    const GOOGLE_SECTION_SETTINGS_TITLE = 'Google Settings';

    /** @var array<string, string> */
    private static $fieldNameMap = array (
        WsclMainOptions::SITE_EMAIL_NAME_KEY => 'Site Name',
        WsclMainOptions::SITE_EMAIL_ADDRESS_KEY => 'General Email Address',
        WsclMainOptions::DEVELOPER_EMAIL_ADDRESS_KEY => 'Developer Email Address',
        WsclMainOptions::DIRECTOR_FIRSTNAME => 'First Name',
        WsclMainOptions::DIRECTOR_FULLNAME => 'Full Name',
        WsclMainOptions::DIRECTOR_EMAIL_ALIAS => 'Email Alias',
        WsclMainOptions::MAILERLITE_API_KEY => 'MailerLite API Key',
        WsclMainOptions::GOOGLE_MAPS_API_KEY => 'Google Maps API Key'
    );

    public function __construct(PluginOptionsInterface $options, LoggerInterface $logger)
    {
        parent::__construct(self::TAB_NAME, $options, $logger);
    }

    public function addSettings(string $pageSlug): void
    {
        /**
         * Site Email Settings section
         */
        add_settings_section(
            self::EMAIL_SECTION_SETTINGS_ID,
            self::EMAIL_SECTION_SETTINGS_TITLE,
            function () { print '<hr>'; },
            $pageSlug
            );

        add_settings_field(
            WsclMainOptions::SITE_EMAIL_NAME_KEY,
            self::$fieldNameMap[WsclMainOptions::SITE_EMAIL_NAME_KEY],    // field Title
            function () {
                $this->renderTextField(
                    WsclMainOptions::SITE_EMAIL_NAME_KEY,
                    'The name of the site to use in email messages',
                    []
                    );
            },  // Callback
            $pageSlug,  // Page
            self::EMAIL_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::SITE_EMAIL_ADDRESS_KEY,
            self::$fieldNameMap[WsclMainOptions::SITE_EMAIL_ADDRESS_KEY],    // field Title
            function () {
                $this->renderEmailField(
                    WsclMainOptions::SITE_EMAIL_ADDRESS_KEY,
                    'Email address to use for messages sent from the web site (e.g. info@washingtonleague.org)',
                    );
            },  // Callback
            $pageSlug,  // Page
            self::EMAIL_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::DEVELOPER_EMAIL_ADDRESS_KEY,
            self::$fieldNameMap[WsclMainOptions::DEVELOPER_EMAIL_ADDRESS_KEY],    // field Title
            function () {
                $this->renderEmailField(
                    WsclMainOptions::DEVELOPER_EMAIL_ADDRESS_KEY,
                    'Email address to use for messages sent to/from the site developer',
                    );
            },  // Callback
            $pageSlug,  // Page
            self::EMAIL_SECTION_SETTINGS_ID  // Section
            );

        /**
         * Executive Director Settings section
         */
        add_settings_section(
            self::DIRECTOR_SECTION_SETTINGS_ID,
            self::DIRECTOR_SECTION_SETTINGS_TITLE,
            function () { print '<hr>'; },
            $pageSlug
            );

        add_settings_field(
            WsclMainOptions::DIRECTOR_FIRSTNAME,
            self::$fieldNameMap[WsclMainOptions::DIRECTOR_FIRSTNAME],    // field Title
            function () {
                $this->renderTextField(
                    WsclMainOptions::DIRECTOR_FIRSTNAME,
                    'First name of the Executive Director',
                    []
                    );
            },  // Callback
            $pageSlug,  // Page
            self::DIRECTOR_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::DIRECTOR_FULLNAME,
            self::$fieldNameMap[WsclMainOptions::DIRECTOR_FULLNAME],    // field Title
            function () {
                $this->renderTextField(
                    WsclMainOptions::DIRECTOR_FULLNAME,
                    'Full name of the Executive Director',
                    []
                    );
            },  // Callback
            $pageSlug,  // Page
            self::DIRECTOR_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::DIRECTOR_EMAIL_ALIAS,
            self::$fieldNameMap[WsclMainOptions::DIRECTOR_EMAIL_ALIAS],    // field Title
            function () {
                $this->renderEmailField(
                    WsclMainOptions::DIRECTOR_EMAIL_ALIAS,
                    'Email alias for the Executive Director (e.g. firstname@washingtonleague.org)',
                    );
            },  // Callback
            $pageSlug,  // Page
            self::DIRECTOR_SECTION_SETTINGS_ID  // Section
            );

        /**
         * Google Settings section
         */
        add_settings_section(
            self::GOOGLE_SECTION_SETTINGS_ID,
            self::GOOGLE_SECTION_SETTINGS_TITLE,
            function () { print '<hr>'; },
            $pageSlug
            );

        add_settings_field(
            WsclMainOptions::GOOGLE_MAPS_API_KEY,
            self::$fieldNameMap[WsclMainOptions::GOOGLE_MAPS_API_KEY],    // field Title
            function () {
                $this->renderTextField(
                    WsclMainOptions::GOOGLE_MAPS_API_KEY,
                    'The API Key to authenticate API requests.'
                    );
            },  // Callback
            $pageSlug,  // Page
            self::GOOGLE_SECTION_SETTINGS_ID  // Section
            );

        /**
         * MailerLite Settings section
         */
        if (!WsclMainOptions::isMailerLitePluginInstalled()) {
            add_settings_section(
                self::MAILERLITE_SECTION_SETTINGS_ID,
                self::MAILERLITE_SECTION_SETTINGS_TITLE,
                function () { print '<hr>'; },
                $pageSlug
                );

            add_settings_field(
                WsclMainOptions::MAILERLITE_API_KEY,
                self::$fieldNameMap[WsclMainOptions::MAILERLITE_API_KEY],    // field Title
                function () {
                    $this->renderTextField(
                        WsclMainOptions::MAILERLITE_API_KEY,
                        'The API Key to authenticate API requests.'
                        );
                },  // Callback
                $pageSlug,  // Page
                self::MAILERLITE_SECTION_SETTINGS_ID  // Section
                );
        }

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param   string  $pageSlug   The page slug for any errors.
     * @param   array<string, mixed> $input Contains all settings fields as array keys
     */
    public function sanitize(string $pageSlug, ?array $input): ?array
    {
        if (!is_null($input)) {
            $this->logger->info('Sanitizing data: ', $input);

            foreach ($input as $key => $value) {
                switch ($key) {
                    case WsclMainOptions::DIRECTOR_FIRSTNAME:
                    case WsclMainOptions::DIRECTOR_FULLNAME:
                    case WsclMainOptions::SITE_EMAIL_NAME_KEY:
                        $this->validateStringValue($key, $value, $pageSlug, self::$fieldNameMap[$key]);
                        break;

                    case WsclMainOptions::DIRECTOR_EMAIL_ALIAS:
                    case WsclMainOptions::SITE_EMAIL_ADDRESS_KEY:
                    case WsclMainOptions::DEVELOPER_EMAIL_ADDRESS_KEY:
                        $this->validateEmailAddress($key, $value, $pageSlug, self::$fieldNameMap[$key]);
                        break;

                    case WsclMainOptions::MAILERLITE_API_KEY:
                        $this->validateMailerLiteApiKey($value, $pageSlug);
                        break;

                    case WsclMainOptions::GOOGLE_MAPS_API_KEY:
                        $this->validateStringValue($key, $value, $pageSlug, self::$fieldNameMap[$key], 'Unexpected error with key');
                        break;

                    default:
                        break;
                }
            }

            $this->logger->info('Post sanitized data:', $this->options->getValues());

            return $this->options->getValues();
        } else {
            return $input;
        }
    }

    private function validateMailerLiteApiKey(string $apiKey, string $pageSlug): void
    {
        $validator = new StringValidator(
            $pageSlug,
            WsclMainOptions::MAILERLITE_API_KEY,
            self::$fieldNameMap[WsclMainOptions::MAILERLITE_API_KEY]
            );

        if ($validator->isValid($apiKey)) {
            if (MailerLiteClient::isValidApiKey($apiKey)) {
                $this->options->setValue(WsclMainOptions::MAILERLITE_API_KEY, trim($apiKey));
            } else {
                $validator->addError('Incorrect Key, not authorized');
            }
        }
    }
}
