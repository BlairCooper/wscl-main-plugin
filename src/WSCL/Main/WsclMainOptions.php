<?php
declare(strict_types = 1);
namespace WSCL\Main;

use RCS\WP\PluginOptions;
use WSCL\Main\CcnBikes\CcnBikesOptionsInterface;
use WSCL\Main\MailerLite\MailerLiteOptionsInterface;
use WSCL\Main\Scholarships\ScholarshipOptionsInterface;

class WsclMainOptions extends PluginOptions
    implements WsclMainOptionsInterface, MailerLiteOptionsInterface, ScholarshipOptionsInterface, CcnBikesOptionsInterface
{
    const OPTIONS_NAME = 'wscl_site_options';

    const SITE_EMAIL_NAME_KEY = 'siteEmailName';
    const SITE_EMAIL_ADDRESS_KEY = 'siteEmailAddress';
    const DEVELOPER_EMAIL_ADDRESS_KEY = 'devEmailAddress';

    const MAILERLITE_API_KEY = 'mailerLiteApiKey';
    const GOOGLE_MAPS_API_KEY = 'googleMapsApiKey';

    const CCN_REST_API_URL_KEY = 'ccnRestApiUrl';
    const CCN_USERNAME_KEY = 'ccnUsername';
    const CCN_PASSWORD_KEY = 'ccnPassword';

    const RACE_RESULT_ACCOUNT_KEY = 'rrAccountNumber';
    const RACE_RESULT_USERNAME_KEY = 'rrUsername';
    const RACE_RESULT_PASSWORD_KEY = 'rrPassword';

    const PDF_SERVICE_URL_KEY = 'pdfServiceUrl';

    // FA => Financial Assistance
    const FA_FALL_FEE = 'faFallFee';
    const FA_FALL_MINIMUM = 'faFallMin';
    const FA_SPRING_FEE = 'faSpringFee';
    const FA_SPRING_MINIMUM = 'faSpringMin';
    const FA_MINIMUM_SCORE = 'faMinimumScore';
    const FA_COACH_FEE = 'faCoachFee';

    const DIRECTOR_FIRSTNAME = 'edFirstName';
    const DIRECTOR_FULLNAME = 'edFullName';
    const DIRECTOR_EMAIL_ALIAS = 'edEmailAlias';

    /**
     *
     * @param mixed ...$args
     * @return WsclMainOptions
     */
    public static function init(...$args): WsclMainOptions
    {
        return parent::init(...$args);
    }

    public function getOptionName(): string
    {
        return self::OPTIONS_NAME;
    }

    protected function getOptionKeys(): array
    {
        return [
            self::MAILERLITE_API_KEY,
            self::GOOGLE_MAPS_API_KEY,
            self::CCN_REST_API_URL_KEY,
            self::CCN_USERNAME_KEY,
            self::CCN_PASSWORD_KEY,
            self::PDF_SERVICE_URL_KEY,
            self::RACE_RESULT_ACCOUNT_KEY,
            self::RACE_RESULT_USERNAME_KEY,
            self::RACE_RESULT_PASSWORD_KEY,
            self::FA_FALL_FEE,
            self::FA_FALL_MINIMUM,
            self::FA_SPRING_FEE,
            self::FA_SPRING_MINIMUM,
            self::FA_MINIMUM_SCORE,
            self::FA_COACH_FEE,
            self::DIRECTOR_FIRSTNAME,
            self::DIRECTOR_FULLNAME,
            self::DIRECTOR_EMAIL_ALIAS,
            self::SITE_EMAIL_NAME_KEY,
            self::SITE_EMAIL_ADDRESS_KEY,
            self::DEVELOPER_EMAIL_ADDRESS_KEY
        ];
    }

    /**
     * Initialize the collections used to maintain the values.
     *
     * @since    1.0.0
     */
    protected function initializeInstance(): void
    {
        parent::initializeInstance();

        if (empty($this->getCcnRestApiUrl())) {
            $this->setValue(self::CCN_REST_API_URL_KEY, 'https://ccnbikes.com/en/rest/v2/');
        }

        if (empty($this->getPdfServiceUrl())) {
            $this->setValue(self::PDF_SERVICE_URL_KEY, 'https://pdfsvc.raincitysolutions.com/xml2pdf/');
        }

        if (empty($this->getRaceResultAccount())) {
            $this->setValue(self::RACE_RESULT_ACCOUNT_KEY, '24205');
        }

        if (empty($this->getRaceResultUsername())) {
            $this->setValue(self::RACE_RESULT_USERNAME_KEY, 'WashingtonStudent');
        }

        if (empty($this->getRaceResultPassword())) {
            $this->setValue(self::RACE_RESULT_PASSWORD_KEY, 'p#fITS%CSchb');
        }

        if (empty($this->getFallSeasonFee())) {
            $this->setValue(self::FA_FALL_FEE, '150');
            $this->setValue(self::FA_FALL_MINIMUM, '30');
            $this->setValue(self::FA_SPRING_FEE, '265');
            $this->setValue(self::FA_SPRING_MINIMUM, '50');
            $this->setValue(self::FA_MINIMUM_SCORE, '25');
            $this->setValue(self::FA_COACH_FEE, '40');
        }

        if (empty($this->getDirectorEmailAlias())) {
            $this->setValue(self::DIRECTOR_FIRSTNAME, 'David');
            $this->setValue(self::DIRECTOR_FULLNAME, 'David Williams');
            $this->setValue(self::DIRECTOR_EMAIL_ALIAS, 'david@washingtonleague.org');
        }

        if (empty($this->getSiteEmailName())) {
            $this->setValue(self::SITE_EMAIL_NAME_KEY, 'WSCL');
            $this->setValue(self::SITE_EMAIL_ADDRESS_KEY, 'info@washingtonleague.org');
        }

        if (empty($this->getDeveloperEmailAddress())) {
            $this->setValue(self::DEVELOPER_EMAIL_ADDRESS_KEY, 'digital.assets@washingtonleague.org');
        }

        $this->save();
    }

    public function getSiteEmailName(): string
    {
        return $this->getValue(self::SITE_EMAIL_NAME_KEY);
    }

    public function getSiteEmailAddress(): string
    {
        return $this->getValue(self::SITE_EMAIL_ADDRESS_KEY);
    }

    public function getMailerLiteApiKey(): ?string
    {
        // Use the setting from the MailerLite plugin if available, otherwise use ours
        return get_option('mailerlite_api_key', $this->getValue(self::MAILERLITE_API_KEY));
    }

    public static function isMailerLitePluginInstalled(): bool
    {
        return false !== get_option('mailerlite_api_key');
    }

    public function getGoogleMapsApiKey(): ?string
    {
        return $this->getValue(self::GOOGLE_MAPS_API_KEY);
    }

    public function getCcnRestApiUrl(): ?string
    {
        return $this->getValue(self::CCN_REST_API_URL_KEY);
    }

    public function getCcnUsername(): ?string
    {
        return $this->getValue(self::CCN_USERNAME_KEY);
    }

    public function getCcnPassword(): ?string
    {
        return $this->getValue(self::CCN_PASSWORD_KEY);
    }

    public function getPdfServiceUrl(): ?string
    {
        return $this->getValue(self::PDF_SERVICE_URL_KEY);
    }

    public function getRaceResultAccount(): ?int
    {
        return intval($this->getValue(self::RACE_RESULT_ACCOUNT_KEY));
    }

    public function getRaceResultUsername(): ?string
    {
        return $this->getValue(self::RACE_RESULT_USERNAME_KEY);
    }

    public function getRaceResultPassword(): ?string
    {
        return $this->getValue(self::RACE_RESULT_PASSWORD_KEY);
    }

    public function getFallSeasonFee(): ?int
    {
        return intval($this->getValue(self::FA_FALL_FEE));
    }

    public function getFallSeasonMinimum(): ?int
    {
        return intval($this->getValue(self::FA_FALL_MINIMUM));
    }

    public function getSpringSeasonFee(): ?int
    {
        return intval($this->getValue(self::FA_SPRING_FEE));
    }

    public function getSpringSeasonMinimum(): ?int
    {
        return intval($this->getValue(self::FA_SPRING_MINIMUM));
    }

    public function getMinimumScore(): ?int
    {
        return intval($this->getValue(self::FA_MINIMUM_SCORE));
    }

    public function getCoachFee(): ?int
    {
        return intval($this->getValue(self::FA_COACH_FEE));
    }

    public function getDirectorFirstName(): string
    {
        return $this->getValue(self::DIRECTOR_FIRSTNAME);
    }

    public function getDirectorFullName(): string
    {
        return $this->getValue(self::DIRECTOR_FULLNAME);
    }

    public function getDirectorEmailAlias(): string
    {
        return $this->getValue(self::DIRECTOR_EMAIL_ALIAS);
    }

    public function getDeveloperEmailAddress(): string
    {
        return $this->getValue(self::DEVELOPER_EMAIL_ADDRESS_KEY);
    }
}
