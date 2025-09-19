<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging;

use RCS\PDF\PDFService;
use RCS\WP\Settings\AdminSettingsTab;
use RCS\WP\Validation\UrlValidator;
use WSCL\Main\WsclMainOptions;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\RaceResult\RaceResultClient;
use Psr\Log\LoggerInterface;
use RCS\WP\PluginOptionsInterface;

class StagingOptionsTab extends AdminSettingsTab
{
    private const TAB_NAME = "Staging Settings";

    const CCN_OPTIONS_SECTION_SETTINGS_ID = 'ccnSettingsSection';
    const CCN_OPTIONS_SECTION_SETTINGS_TITLE = 'CCN Settings';
    const RR_OPTIONS_SECTION_SETTINGS_ID = 'rrSettingsSection';
    const RR_OPTIONS_SECTION_SETTINGS_TITLE = 'RaceResult Settings';
    const PDF_OPTIONS_SECTION_SETTINGS_ID = 'pdfSettingsSection';
    const PDF_OPTIONS_SECTION_SETTINGS_TITLE = 'PDF Service Settings';

    /** @var array<string, string> */
    private static $fieldNameMap = array (
        WsclMainOptions::CCN_REST_API_URL_KEY => 'CCN Rest Api URL',
        WsclMainOptions::CCN_USERNAME_KEY => 'Username',
        WsclMainOptions::CCN_PASSWORD_KEY => 'Password',
        WsclMainOptions::PDF_SERVICE_URL_KEY => 'PDF Service URL',
        WsclMainOptions::RACE_RESULT_ACCOUNT_KEY => 'RaceResult Account Number',
        WsclMainOptions::RACE_RESULT_USERNAME_KEY => 'RaceResult Username',
        WsclMainOptions::RACE_RESULT_PASSWORD_KEY => 'RaceResult Password'
    );

    public function __construct(PluginOptionsInterface $options, LoggerInterface $logger)
    {
        parent::__construct(self::TAB_NAME, $options, $logger);
    }

    public function addSettings(string $pageSlug): void
    {
        /**
         * Settings section
         */
        add_settings_section(
            self::CCN_OPTIONS_SECTION_SETTINGS_ID,
            self::CCN_OPTIONS_SECTION_SETTINGS_TITLE,
            function () { print '<hr>'; },
            $pageSlug
            );

        add_settings_field(
            WsclMainOptions::CCN_REST_API_URL_KEY,
            self::$fieldNameMap[WsclMainOptions::CCN_REST_API_URL_KEY],    // field Title
            function () {
                $this->renderUrlField(
                    WsclMainOptions::CCN_REST_API_URL_KEY,
                    'The URL for the CCN server URL (e.g. https://ccnbikes.com/en/rest/v2/).',
                    array (
                        'size'      => 64,
                        'maxlength' => 64,
                        'required'  => null
                    )

                    );
            },  // Callback
            $pageSlug,  // Page
            $this::CCN_OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::CCN_USERNAME_KEY,
            self::$fieldNameMap[WsclMainOptions::CCN_USERNAME_KEY],    // field Title
            function () {
                $this->renderEmailField(
                    WsclMainOptions::CCN_USERNAME_KEY,
                    'The username for CCN (e.g. info@washingtonleague.org).',
                    false,
                    array (
                        'size'      => 40,
                        'maxlength' => 64,
                        'required'  => null
                    )
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::CCN_OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::CCN_PASSWORD_KEY,
            self::$fieldNameMap[WsclMainOptions::CCN_PASSWORD_KEY],    // field Title
            function () {
                $this->renderPasswordField(
                    WsclMainOptions::CCN_PASSWORD_KEY,
                    'The password associated with the username.',
                    array (
                        'size'      => 40,
                        'maxlength' => 64,
                        'required'  => null
                    )
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::CCN_OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_section(
            self::RR_OPTIONS_SECTION_SETTINGS_ID,
            self::RR_OPTIONS_SECTION_SETTINGS_TITLE,
            function () { print '<hr>'; },
            $pageSlug
            );

        add_settings_field(
            WsclMainOptions::RACE_RESULT_ACCOUNT_KEY,
            self::$fieldNameMap[WsclMainOptions::RACE_RESULT_ACCOUNT_KEY],    // field Title
            function () {
                $this->renderNumberField(
                    WsclMainOptions::RACE_RESULT_ACCOUNT_KEY,
                    'The RaceResult account number',
                    array (
                    )
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::RR_OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::RACE_RESULT_USERNAME_KEY,
            self::$fieldNameMap[WsclMainOptions::RACE_RESULT_USERNAME_KEY],    // field Title
            function () {
                $this->renderTextField(
                    WsclMainOptions::RACE_RESULT_USERNAME_KEY,
                    'The RaceResult username',
                    array (
                        'size'      => 40,
                        'maxlength' => 64
                    )
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::RR_OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::RACE_RESULT_PASSWORD_KEY,
            self::$fieldNameMap[WsclMainOptions::RACE_RESULT_PASSWORD_KEY],    // field Title
            function () {
                $this->renderPasswordField(
                    WsclMainOptions::RACE_RESULT_PASSWORD_KEY,
                    'The RaceResult password',
                    array (
                        'size'      => 40,
                        'maxlength' => 64
                    )
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::RR_OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_section(
            self::PDF_OPTIONS_SECTION_SETTINGS_ID,
            self::PDF_OPTIONS_SECTION_SETTINGS_TITLE,
            function () { print '<hr>'; },
            $pageSlug
            );

        add_settings_field(
            WsclMainOptions::PDF_SERVICE_URL_KEY,
            self::$fieldNameMap[WsclMainOptions::PDF_SERVICE_URL_KEY],    // field Title
            function () {
                $this->renderUrlField(
                    WsclMainOptions::PDF_SERVICE_URL_KEY,
                    'The URL for the PDF service URL (e.g. https://pdfsvc.raincitysolutions.com/xml2pdf/).',
                    array (
                        'size'      => 64,
                        'maxlength' => 64,
                        'required'  => null
                    )

                    );
            },  // Callback
            $pageSlug,  // Page
            $this::PDF_OPTIONS_SECTION_SETTINGS_ID  // Section
            );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param   string  $pageSlug   The page slug for any errors.
     * @param   array<string, mixed> $input Contains all settings fields as array keys
     */
    public function sanitize(string $pageSlug, ?array $input): ?array
    {
        $result = $input;

        if (!is_null($input)) {
            $this->logger->info('Sanitizing data: ', $input);
            foreach ($input as $key => $value) {
                switch ($key) {
                    case WsclMainOptions::CCN_REST_API_URL_KEY:
                        $this->validateCcnRestApiUrl($key, $value, $pageSlug);
                        break;

                    case WsclMainOptions::CCN_USERNAME_KEY:
                        $this->validateStringValue($key, $value, $pageSlug, self::$fieldNameMap[$key], 'Invalid CCN username');
                        break;

                    case WsclMainOptions::CCN_PASSWORD_KEY:
                        $this->validateStringValue($key, $value, $pageSlug, self::$fieldNameMap[$key], 'Invalid CCN password');
                        break;

                    case WsclMainOptions::PDF_SERVICE_URL_KEY:
                        $this->validatePdfServiceUrl($key, $value, $pageSlug);
                        break;

                    case WsclMainOptions::RACE_RESULT_ACCOUNT_KEY:
                        $this->validateNumericValue($key, $value, $pageSlug, self::$fieldNameMap[$key], null, null, 'Invalid RaceResult account');
                        break;

                    case WsclMainOptions::RACE_RESULT_USERNAME_KEY:
                        $this->validateStringValue($key, $value, $pageSlug, self::$fieldNameMap[$key], 'Invalid RaceResult username');
                        break;

                    case WsclMainOptions::RACE_RESULT_PASSWORD_KEY:
                        $this->validateStringValue($key, $value, $pageSlug, self::$fieldNameMap[$key], 'Invalid RaceResult password');
                        break;

                    default:
                        break;
                }
            }

            $this->checkCcnAuthentication($pageSlug, $input);
            $this->checkRaceResultAuthentication($pageSlug, $input);

            $this->logger->info('Post sanitized data:', $this->options->getValues());

            $result = $this->options->getValues();
        }

        return $result;
    }

    private function validateCcnRestApiUrl(string $key, string $value, string $pageSlug): void
    {
        $validator = new UrlValidator($pageSlug, $key, self::$fieldNameMap[$key]);
        if ($validator->isValid($value)) {
            if (substr($value, strlen($value)-1) !== '/') {
                $value .= '/';
            }

            $jsonBody = file_get_contents($value);
            if ($jsonBody) {
                $json = json_decode($jsonBody);

                if (isset($json) && isset($json->{'membership_app/identity-memberships'})) {
                    $this->options->setValue($key, trim($value));
                } else {
                    $validator->addError('Does not appear to refer to the CCN REST API');
                }
            } else {
                $validator->addError('Does not appear to refer to the CCN REST API');
            }
        } else {
            $validator->addError("Invalid server");
        }
    }

    private function validatePdfServiceUrl(string $key, string $value, string $pageSlug): void
    {
        $validator = new UrlValidator($pageSlug, $key, self::$fieldNameMap[$key]);
        if ($validator->isValid($value)) {
            if (substr($value, strlen($value)-1) !== '/') {
                $value .= '/';
            }

            try {
                $pdfSvc = new PDFService($value, $this->logger);
                if ($pdfSvc->isServiceActive()) {
                    $this->options->setValue($key, trim($value));
                } else {
                    $validator->addError('Does not appear to refer to a PDF Service provider');
                }
            } catch (\RCS\PDF\PDFServiceException $pse) {
                $validator->addError('Does not appear to refer to a PDF Service provider');
            }
        } else {
            $validator->addError("Invalid URL");
        }
    }

    /**
     *
     * @param string $pageSlug
     * @param string[] $input
     */
    private function checkCcnAuthentication(string $pageSlug, ?array $input): void
    {
        global $wp_settings_errors;

        if (empty($wp_settings_errors) &&
            isset($input[WsclMainOptions::CCN_REST_API_URL_KEY]) &&
            isset($input[WsclMainOptions::CCN_USERNAME_KEY]) &&
            isset($input[WsclMainOptions::CCN_PASSWORD_KEY])
            )
        {
            if (!CcnClient::areCredentialsValid(
                $input[WsclMainOptions::CCN_REST_API_URL_KEY],
                $input[WsclMainOptions::CCN_USERNAME_KEY],
                $input[WsclMainOptions::CCN_PASSWORD_KEY]
                )
                )
            {
                \add_settings_error(
                    $pageSlug,
                    WsclMainOptions::CCN_REST_API_URL_KEY,
                    'Unable to authenticate against the CCN server, bad username or password?',
                    'error'
                    );
            }
        }
    }

    /**
     *
     * @param string $pageSlug
     * @param mixed[] $input
     */
    private function checkRaceResultAuthentication(string $pageSlug, ?array $input): void
    {
        global $wp_settings_errors;

        if (empty($wp_settings_errors) &&
            isset($input[WsclMainOptions::RACE_RESULT_ACCOUNT_KEY]) &&
            isset($input[WsclMainOptions::RACE_RESULT_USERNAME_KEY]) &&
            isset($input[WsclMainOptions::RACE_RESULT_PASSWORD_KEY])
            ) {
                if (!RaceResultClient::isValidConfiguration(
                    $input[WsclMainOptions::RACE_RESULT_USERNAME_KEY],
                    $input[WsclMainOptions::RACE_RESULT_PASSWORD_KEY],
                    $this->logger
                    ))
                {
                    \add_settings_error(
                        $pageSlug,
                        WsclMainOptions::RACE_RESULT_USERNAME_KEY,
                        'Unable to authenticate against the RaceResult server, bad username or password?',
                        'error'
                        );

                }
            }
    }

}
