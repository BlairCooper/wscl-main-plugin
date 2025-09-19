<?php
declare(strict_types = 1);
namespace WSCL\Main\Scholarships;

use Psr\Log\LoggerInterface;
use RCS\WP\PluginOptionsInterface;
use RCS\WP\Settings\AdminSettingsTab;
use RCS\WP\Validation\NumberValidator;
use WSCL\Main\WsclMainOptions;

class ScholarshipOptionsTab extends AdminSettingsTab
{
    private const TAB_NAME = "Scholarships";

    const OPTIONS_SECTION_SETTINGS_ID = 'settingsSection';
    const OPTIONS_SECTION_SETTINGS_TITLE = 'Scholarship Settings';

    /** @var array<string, string> */
    private static $fieldNameMap = array (
        WsclMainOptions::FA_FALL_FEE => 'Fall Registration Fee',
        WsclMainOptions::FA_FALL_MINIMUM => 'Fall Minimum Scholarship',
        WsclMainOptions::FA_SPRING_FEE => 'Spring Registration Fee',
        WsclMainOptions::FA_SPRING_MINIMUM => 'Spring Minimum Scholarship',
        WsclMainOptions::FA_MINIMUM_SCORE => 'Minimum Score',
        WsclMainOptions::FA_COACH_FEE => 'Annual Coach Fee'
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
            self::OPTIONS_SECTION_SETTINGS_ID,
            self::OPTIONS_SECTION_SETTINGS_TITLE,
            function () { print '<hr>'; },
            $pageSlug
            );

        add_settings_field(
            WsclMainOptions::FA_SPRING_FEE,
            self::$fieldNameMap[WsclMainOptions::FA_SPRING_FEE],    // field Title
            function () {
                $this->renderNumberField(
                    WsclMainOptions::FA_SPRING_FEE,
                    'The fee for students to participate in the Spring Season.',
                    [
                        'size'      => 3,
                        'maxlength' => 3
                    ]
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::FA_SPRING_MINIMUM,
            self::$fieldNameMap[WsclMainOptions::FA_SPRING_MINIMUM],    // field Title
            function () {
                $this->renderNumberField(
                    WsclMainOptions::FA_SPRING_MINIMUM,
                    'The minimum scholarship that would be awarded for the Spring Season. Generally, 20% of the full fee, rounded to the nearest $5.',
                    [
                        'size'      => 3,
                        'maxlength' => 3
                    ]
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::FA_FALL_FEE,
            self::$fieldNameMap[WsclMainOptions::FA_FALL_FEE],    // field Title
            function () {
                $this->renderNumberField(
                    WsclMainOptions::FA_FALL_FEE,
                    'The fee for students to participate in the Fall Season.',
                    [
                        'size'      => 3,
                        'maxlength' => 3
                    ]
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::FA_FALL_MINIMUM,
            self::$fieldNameMap[WsclMainOptions::FA_FALL_MINIMUM],    // field Title
            function () {
                $this->renderNumberField(
                    WsclMainOptions::FA_FALL_MINIMUM,
                    'The minimum scholarship that would be awarded for the Fall Season. Generally, 20% of the full fee, rounded to the nearest $5.',
                    [
                        'size'      => 3,
                        'maxlength' => 3
                    ]
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::FA_MINIMUM_SCORE,
            self::$fieldNameMap[WsclMainOptions::FA_MINIMUM_SCORE],    // field Title
            function () {
                $this->renderNumberField(
                    WsclMainOptions::FA_MINIMUM_SCORE,
                    'The minimum score to get a full scholarship.',
                    [
                        'size'      => 2,
                        'maxlength' => 2
                    ]
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::OPTIONS_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclMainOptions::FA_COACH_FEE,
            self::$fieldNameMap[WsclMainOptions::FA_COACH_FEE],    // field Title
            function () {
                $this->renderNumberField(
                    WsclMainOptions::FA_COACH_FEE,
                    '',
                    [
                        'size'      => 3,
                        'maxlength' => 3
                    ]
                    );
            },  // Callback
            $pageSlug,  // Page
            $this::OPTIONS_SECTION_SETTINGS_ID  // Section
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
        if (!is_null($input)) {
            $this->logger->info('Sanitizing data: ', $input);

            foreach ($input as $key => $value) {
                switch ($key) {
                    case WsclMainOptions::FA_FALL_FEE:
                    case WsclMainOptions::FA_SPRING_FEE:
                        $this->validateNumericValue(
                            $key,
                            $value,
                            $pageSlug,
                            self::$fieldNameMap[$key],
                            100,
                            500
                        );
                        break;

                    case WsclMainOptions::FA_FALL_MINIMUM:
                        $this->validateMinimum(
                            $key,
                            $value,
                            intval($this->options->getValue(WsclMainOptions::FA_FALL_FEE)),
                            $pageSlug
                        );
                        break;

                    case WsclMainOptions::FA_SPRING_MINIMUM:
                        $this->validateMinimum(
                            $key,
                            $value,
                            intval($this->options->getValue(WsclMainOptions::FA_SPRING_FEE)),
                            $pageSlug);
                        break;

                    case WsclMainOptions::FA_MINIMUM_SCORE:
                        $this->validateNumericValue(
                            $key,
                            $value,
                            $pageSlug,
                            self::$fieldNameMap[$key],
                            20,
                            50
                        );
                        break;

                    case WsclMainOptions::FA_COACH_FEE:
                        $this->validateNumericValue(
                            $key,
                            $value,
                            $pageSlug,
                            self::$fieldNameMap[$key],
                            10,
                            100
                        );
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

    private function validateMinimum(string $fieldKey, mixed $minimum, int $fee, string $pageSlug): void
    {
        $validator = new NumberValidator($pageSlug, $fieldKey, self::$fieldNameMap[$fieldKey]);
        $validator->setRange(10, 200);

        if ($validator->isValid($minimum)) {
            $intMin = intval($minimum);

            if ($intMin >= $fee) {
                $validator->addError('Minimum cannot be equal to or more that the fee.');
            } elseif ($intMin > ($fee / 4)) {
                $validator->addError('Minimum cannot be more than 25% of the fee.');
            } else {
                $this->options->setValue($fieldKey, $minimum);
            }
        }
    }
}
