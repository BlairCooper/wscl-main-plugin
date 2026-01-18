<?php
declare(strict_types = 1);
namespace WSCL\Main\Scholarships;

use Psr\Log\LoggerInterface;
use RCS\WP\Formidable\Formidable;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\CcnBikes\Entity\Coupon;

class ScholarshipsHelper
{
    const COUPON_CODE_SIZE = 8;

    const FORM_SCHOLARSHIP_REQUEST = 'wscl_scholarship_application';
    const FORM_COACH_SCHOLARSHIP = 'wscl_coach_scholarship';
    const FORM_STUDENT_SCHOLARSHIP = 'wscl_student_scholarship';

    // Main form
    const FLD_HEADER_HTML = 'fa_header_html';
    const FLD_INTRO_HTML = 'fa_intro_html';
    const FLD_STUDENT_COACH = 'fa_student_coach';
    const FLD_COACH_FORM = 'fa_coach_form';
    const FLD_STUDENT_FORM = 'fa_student_form';
    const FLD_CAPTCHA = 'fa_captcha';
    const FLD_SUBMIT_BUTTON = 'fa_submit';

    // Coach Form
    const FLD_COACH_NAME = 'fa_c_name';
    const FLD_COACH_TEAM = 'fa_c_team';
    const FLD_COACH_EMAIL = 'fa_c_email';
    const FLD_COACH_PHONE = 'fa_c_phone';
    const FLD_COACH_ADDRESS = 'fa_c_address';
    const FLD_COACH_WHAT_FOR = 'fa_c_what_for';
    const FLD_COACH_CLINIC_DESC = 'fa_c_clinic_desc';
    const FLD_COACH_CLINIC_COST = 'fa_c_clinic_cost';
    const FLD_COACH_CAN_PAY = 'fld_c_can_pay';
    const FLD_COACH_RATIONAL = 'fa_c_rational';
    const FLD_COACH_AWARD_AMOUNT = 'fa_c_award_amount';
    const FLD_COACH_COUPON_CODE = 'fa_c_coupon_code';
    const FLD_COACH_COUPON_USED = 'fa_c_coupon_used';
    const FLD_COACH_SUBMIT = 'fa_c_submit';

    // Student form
    const FLD_STUDENT_NAME = 'fa_s_name';
    const FLD_STUDENT_TEAM = 'fa_s_team';
    const FLD_STUDENT_GRADE = 'fa_s_grade';
    const FLD_STUDENT_PARENT = 'fa_s_parent';
    const FLD_STUDENT_EMAIL = 'fa_s_email';
    const FLD_STUDENT_PHONE = 'fa_s_phone';
    const FLD_STUDENT_ADDRESS = 'fa_s_address';
    const FLD_STUDENT_WHAT_FOR = 'fa_s_what_for';
    const FLD_STUDENT_CLINIC_DESC = 'fa_s_clinic_desc';
    const FLD_STUDENT_CLINIC_COST = 'fa_s_clinic_cost';
    const FLD_STUDENT_CAN_PAY = 'fa_s_can_pay';

    const FLD_STUDENT_FREE_LUNCH = 'fa_s_free_lunch';
    const FLD_STUDENT_ANNUAL_INCOME = 'fa_s_annual_income';
    const FLD_STUDENT_CHILD_CNT = 'fa_s_child_cnt';
    const FLD_STUDENT_PARENT_CNT = 'fa_s_parent_cnt';
    const FLD_STUDENT_OTHER_ASSISTANCE = 'fa_s_other_assistance';
    const FLD_STUDENT_OTHER_SPORTS = 'fa_s_other_sports';
    const FLD_STUDENT_OTHER_SPORTS_FA = 'fs_s_other_sports_fa';
    const FLD_STUDENT_SIBLINGS = 'fs_s_siblings';

    const FLD_STUDENT_RATIONAL = 'fa_s_rational';
    const FLD_STUDENT_SCORE = 'fa_s_score';
    const FLD_STUDENT_AWARD_AMOUNT = 'fa_s_award_amount';
    const FLD_STUDENT_COUPON_CODE = 'fa_s_coupon_code';
    const FLD_STUDENT_COUPON_USED = 'fa_s_coupon_used';
    const FLD_STUDENT_SUBMIT = 'fa_s_submit';

    const LEAGUE_FEE = 'League Fee';

    public function __construct(
        private CcnClient $ccnClient,
        private ScholarshipOptionsInterface $options,
        private LoggerInterface $logger
        )
    {
        add_filter('frm_use_embedded_form_actions', [$this, 'frmUseEmbeddedFormActions'], 10, 2);
        add_filter('frm_pre_create_entry', [$this, 'frmPreCreateEntry']);

        add_action('frm_after_create_entry', [$this, 'frmAfterCreateEntry'], 20, 2);
        add_action('frm_after_update_entry', [$this, 'frmAfterUpdateEntry'], 10, 2);

        // Hooks to log email notifications
        add_filter('frm_email_header', [$this, 'frmEmailHeader'], 20, 2);
        add_action('frm_notification', [$this, 'frmNotification'], 20, 3);
    }

    /**
     *
     * @param bool $triggerActions
     * @param array<string, mixed> $args
     *
     * @return bool
     */
    public function frmUseEmbeddedFormActions(bool $triggerActions, array $args): bool
    {
        $formObj = $args['form'];

        if (Formidable::getFormId(self::FORM_COACH_SCHOLARSHIP) == $formObj->id ||
            Formidable::getFormId(self::FORM_STUDENT_SCHOLARSHIP) == $formObj->id
            )
        {
            if (Formidable::getFormId(self::FORM_COACH_SCHOLARSHIP) == $formObj->id) {
                $fieldId = Formidable::getFieldId(self::FLD_COACH_COUPON_USED);
            } else {
                $fieldId = Formidable::getFieldId(self::FLD_STUDENT_COUPON_USED);
            }

            // If the coupon has already been used there is no point in sending any notifications
            if ($args['entry']->metas[$fieldId] != 1) {
                $this->logger->info('Allowing actions for coach or student form');
                $triggerActions = true;
            }
        }

        return $triggerActions;
    }

    /**
     *
     * @param array<mixed> $values
     *
     * @return array<string, mixed>
     */
    public function frmPreCreateEntry(array $values): array
    {
        switch ($values['form_id']) {
            case Formidable::getFormId(self::FORM_COACH_SCHOLARSHIP):
                $this->coachFormPreCreate($values);
                break;
            case Formidable::getFormId(self::FORM_STUDENT_SCHOLARSHIP):
                $this->studentFormPreCreate($values);
                break;

            default:
                break;
        }

        return $values;
    }

    public function frmAfterCreateEntry(int $entryId, int $formId): void
    {
        switch ($formId) {
            case Formidable::getFormId(self::FORM_COACH_SCHOLARSHIP):
//                $this->coachFormAfterCreate($entryId);
                break;

            case Formidable::getFormId(self::FORM_STUDENT_SCHOLARSHIP):
                $this->studentFormAfterCreate($entryId);
                break;

            default:
                break;
        }
    }

    public function frmAfterUpdateEntry(int $entryId, int $formId): void
    {
        switch ($formId) {
            case Formidable::getFormId(self::FORM_COACH_SCHOLARSHIP):
                $this->coachFormAfterUpdate($entryId);
                break;
            case Formidable::getFormId(self::FORM_STUDENT_SCHOLARSHIP):
                $this->studentFormAfterUpdate($entryId);
                break;

            default:
                break;
        }
    }


    /************************************************************************
     * Coach form functions
     ***********************************************************************/
    /**
     *
     * @param array<string, mixed> $values
     */
    private function coachFormPreCreate(array &$values): void
    {
        $metas = &$values['item_meta'];

        if (empty($metas[Formidable::getFieldId(self::FLD_COACH_COUPON_CODE)])) {
            $metas[Formidable::getFieldId(self::FLD_COACH_AWARD_AMOUNT)] = 0;
        }
    }

    private function coachFormAfterUpdate(int $entryId): void
    {
        /** @var \stdClass|NULL */
        $entry = \FrmEntry::getOne($entryId, true);

        if (isset($entry)) {
            if (isset($entry->metas[Formidable::getFieldId(self::FLD_COACH_AWARD_AMOUNT)]) &&
                0 != $entry->metas[Formidable::getFieldId(self::FLD_COACH_AWARD_AMOUNT)]
                )
            {
                // If code exists, the update the coupon, otherwise create it
                if (isset($entry->metas[Formidable::getFieldId(self::FLD_COACH_COUPON_CODE)])) {
                    $couponCode = $entry->metas[Formidable::getFieldId(self::FLD_COACH_COUPON_CODE)];
                    $awardAmount = $entry->metas[Formidable::getFieldId(self::FLD_COACH_AWARD_AMOUNT)];

                    $couponResp = $this->ccnClient->findCoupons($couponCode);
                    if (isset($couponResp)) {
                        $coupon = $couponResp->getCoupon($couponCode);

                        if (isset($coupon)) {
                            $coupon->setValue($awardAmount);

                            $this->ccnClient->updateCoupon($coupon);
                        }
                    }
                } else {
                    $couponCode = $this->getCouponCode();

                    $metaId = \FrmEntryMeta::add_entry_meta(
                        $entryId,
                        Formidable::getFieldId(self::FLD_COACH_COUPON_CODE),
                        '',
                        $couponCode
                        );
                    \FrmEntryMeta::update_entry_meta($entryId, Formidable::getFieldId(self::FLD_COACH_COUPON_USED), '', 0);

                    if (0 != $metaId) {
                        $description = sprintf('%s Coach Scholarship %d', self::getSeasonPrefix(), $entryId);
                        $awardAmount = $entry->metas[Formidable::getFieldId(self::FLD_COACH_AWARD_AMOUNT)];

                        $this->createCcnCoupon(
                            $couponCode,
                            $description,
                            $awardAmount
                            );
                    }
                }
            } else {
                $this->logger->info('No award amount set for coach record with id of {id} on update', ['id' => $entryId]);
            }
        } else {
            $this->logger->critical('Unable to find coach record with id of {id} on update', ['id' => $entryId]);
        }
    }


    /************************************************************************
     * Student form functions
     ***********************************************************************/
    /**
     *
     * @param array<string, mixed> $values
     */
    private function studentFormPreCreate(array &$values): void
    {
        $metas = &$values['item_meta'];

        if (empty($metas[Formidable::getFieldId(self::FLD_STUDENT_COUPON_CODE)])) {
            $this->checkOtherSportsAid($metas);

            $score = $this->calculateStudentScore($metas);

            $metas[Formidable::getFieldId(self::FLD_STUDENT_SCORE)] = $score;

            if (self::LEAGUE_FEE == $metas[Formidable::getFieldId(self::FLD_STUDENT_WHAT_FOR)]) {
                if ($score > 0) {
                    $metas[Formidable::getFieldId(self::FLD_STUDENT_AWARD_AMOUNT)] = $this->calculateStudentAward($score);
                    $metas[Formidable::getFieldId(self::FLD_STUDENT_COUPON_CODE)] = $this->getCouponCode();
                } else {
                    $metas[Formidable::getFieldId(self::FLD_STUDENT_AWARD_AMOUNT)] = 0;
                    $metas[Formidable::getFieldId(self::FLD_STUDENT_COUPON_USED)] = 'N/A';
                }
            } else {
                // If not registration fee, set award to 0 as this will require someone to look at the request
                $metas[Formidable::getFieldId(self::FLD_STUDENT_AWARD_AMOUNT)] = 0;
                $metas[Formidable::getFieldId(self::FLD_STUDENT_COUPON_USED)] = 'N/A';
            }
        }
    }

    /**
     * Checks if there is a value for Other Sports Financial Aid even though
     * the user ultimately said they did not participate in other sports. If
     * that is the case, remove the Other Sports Financial Aid response so
     * the score is calculated correctly.
     *
     * @param array<mixed> $metas Array of Formidable field metadata
     */
    private function checkOtherSportsAid(array &$metas): void
    {
        if (isset($metas[Formidable::getFieldId(self::FLD_STUDENT_OTHER_SPORTS_FA)])) {
            $otherSportsResponse = Formidable::getFieldOptionLabel(
                Formidable::getFieldId(self::FLD_STUDENT_OTHER_SPORTS),
                $metas[Formidable::getFieldId(self::FLD_STUDENT_OTHER_SPORTS)]
                );
            if ('No' == $otherSportsResponse) {
                unset($metas[Formidable::getFieldId(self::FLD_STUDENT_OTHER_SPORTS_FA)]);
            }
        }
    }

    private function studentFormAfterCreate(int $entryId): void
    {
        $entry = \FrmEntry::getOne($entryId, true);

        if (isset($entry)) {
            $metas = &$entry->metas;

            if (isset($metas[Formidable::getFieldId(self::FLD_STUDENT_AWARD_AMOUNT)])) {
                $awardAmount = intval($metas[Formidable::getFieldId(self::FLD_STUDENT_AWARD_AMOUNT)]);

                if (0 != $awardAmount) {
                    $this->createStudentCoupon(
                        $metas[Formidable::getFieldId(self::FLD_STUDENT_COUPON_CODE)],
                        $awardAmount,
                        $entryId
                        );
                }
            }
        } else {
            $this->logger->critical('Unable to find student record with id of {id} after create', ['id' => $entryId]);
        }
    }

    private function studentFormAfterUpdate(int $entryId): void
    {
        $entry = \FrmEntry::getOne($entryId, true);

        if (isset($entry)) {
            $metas = &$entry->metas;

            // If there is an award amount and the coupon hasn't been used
            if (isset($metas[Formidable::getFieldId(self::FLD_STUDENT_AWARD_AMOUNT)]) &&
                0 == $metas[Formidable::getFieldId(self::FLD_STUDENT_COUPON_USED)])
            {
                $awardAmount = intval($metas[Formidable::getFieldId(self::FLD_STUDENT_AWARD_AMOUNT)]);

                if ($awardAmount > 0) {
                    if (isset($metas[Formidable::getFieldId(self::FLD_STUDENT_COUPON_CODE)])) {
                        $this->updateStudentCoupon($metas, $awardAmount);
                    } else {
                        $couponCode = $this->getCouponCode();

                        $metaId = \FrmEntryMeta::add_entry_meta(
                            $entryId,
                            Formidable::getFieldId(self::FLD_STUDENT_COUPON_CODE),
                            '',
                            $couponCode
                            );

                        if (0 != $metaId) {
                            $this->createStudentCoupon($couponCode, $awardAmount, $entryId);
                        }
                    }
                }
            }
        } else {
            $this->logger->critical('Unable to find student record with id of {id} on update', ['id' => $entryId]);
        }
    }

    private function createStudentCoupon(string $couponCode, int $awardAmount, int $entryId): void
    {
        $description = sprintf('%s Student Scholarship (%d)', self::getSeasonPrefix(), $entryId);

        $this->createCcnCoupon($couponCode, $description, $awardAmount);
    }

    /**
     *
     * @param array<mixed> $metas
     * @param int $awardAmount
     */
    private function updateStudentCoupon(array $metas, int $awardAmount): void
    {
        $couponCode = $metas[Formidable::getFieldId(self::FLD_STUDENT_COUPON_CODE)];

        $couponResp = $this->ccnClient->findCoupons($couponCode);
        if (isset($couponResp)) {
            $coupon = $couponResp->getCoupon($couponCode);

            if (isset($coupon) && $coupon->getValue() != $awardAmount) {
                $coupon->setValue($awardAmount);

                $this->ccnClient->updateCoupon($coupon);
            }
        }
    }

    /**
     *
     * @param array<int, mixed> $metas
     *
     * @return int
     */
    private function calculateStudentScore(array $metas): int
    {
        $score = 0;
        $score += intval($metas[Formidable::getFieldId(self::FLD_STUDENT_FREE_LUNCH)] ?? 0);
        $score += intval($metas[Formidable::getFieldId(self::FLD_STUDENT_ANNUAL_INCOME)] ?? 0);
        $score += intval($metas[Formidable::getFieldId(self::FLD_STUDENT_CHILD_CNT)] ?? 0);
        $score += intval($metas[Formidable::getFieldId(self::FLD_STUDENT_PARENT_CNT)] ?? 0);
        $score += intval($metas[Formidable::getFieldId(self::FLD_STUDENT_OTHER_ASSISTANCE)] ?? 0);
        $score += intval($metas[Formidable::getFieldId(self::FLD_STUDENT_OTHER_SPORTS)] ?? 0);
        $score += intval($metas[Formidable::getFieldId(self::FLD_STUDENT_OTHER_SPORTS_FA)] ?? 0);
        $score += intval($metas[Formidable::getFieldId(self::FLD_STUDENT_SIBLINGS)] ?? 0);

        return $score;
    }

    private function calculateStudentAward(int $score): int
    {
        $fee = self::isSpringSeason() ? $this->options->getSpringSeasonFee() : $this->options->getFallSeasonFee();
        $min = self::isSpringSeason() ? $this->options->getSpringSeasonMinimum() : $this->options->getFallSeasonMinimum();
        $minScore = $this->options->getMinimumScore();

        $dollarsPerPoint = ($fee-$min)/$minScore;

        // $score could be negative in situations where annual income disqualfies someone
        // The min() call ensures that no more than the fee is awarded
        // The max() call ensures someone with negative score receives a $0 award
        return max(0, min(round((($dollarsPerPoint*$score)+$min) / 5) * 5, $fee));
    }

    private function createCcnCoupon(string $code, string $description, int $awardAmount): bool
    {
        $result = false;
        $coupon = Coupon::initCoupon($code, $description, $awardAmount);

        if (!is_null($this->ccnClient->createCoupon($coupon))) {
            $result = true;
        }

        return $result;
    }

    /**
     * Fetch a unique coupon code.
     *
     * Generates a coupon code that is not already in use in the database.
     *
     * @return string A coupon code
     */
    private function getCouponCode(): string
    {
        // Fetch the current coach and student coupon code entries
        $coachEntries = \FrmEntryMeta::getAll(['it.field_id' => Formidable::getFieldId(self::FLD_COACH_COUPON_CODE)]);
        $studentEntries = \FrmEntryMeta::getAll(['it.field_id' => Formidable::getFieldId(self::FLD_STUDENT_COUPON_CODE)]);

        // Build the set of coupon codes
        $existingCodes = array_map(
            fn($entry): string => $entry->meta_value,
            array_merge($coachEntries, $studentEntries)
            );

        // Generate coupon codes until we find one that isn't already in the database
        do {
            $code = $this->generateCouponCode();
        } while(in_array($code, $existingCodes));

        return $code;
    }

    private function generateCouponCode(): string
    {
        $result = '';

        $chars = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";
        $charsLen = strlen($chars);

        for ($i = 0; $i < self::COUPON_CODE_SIZE; $i++) {
            $result .= $chars[mt_rand(0, $charsLen-1)];
        }

        return $result;
    }

    public static function isSpringSeason(): bool
    {
        $today = new \DateTime();
        $midYear = new \DateTime($today->format('Y') . '-07-01');

        return $today < $midYear;
    }

    public static function getSeasonPrefix(): string
    {
        if (self::isSpringSeason()) {
            $prefix = 'Spring';
        } else {
            $prefix = 'Fall';
        }

        $prefix .= ' '. (new \DateTime())->format('Y');

        return $prefix;
    }

    /**
     *
     * @param array<mixed> $headers
     * @param array<string, mixed> $args
     *
     * @return array<mixed>
     */
    public function frmEmailHeader(array $headers, array $args): array
    {
        if (is_array($args['to_email'])) {
            $recipient = join(', ', $args['to_email']);
        } else {
            $recipient = $args['to_email'];
        }

        $this->logger->info(
            'Preparing to sent notification to {recipient}: {subject}',
            [
                'recipient' => $recipient,
                'subject' => $args['subject']
            ]
            );

        return $headers;
    }

    /**
     * @param string|string[] $recipient
     * @param string $subject
     * @param string $message
     */
    public function frmNotification($recipient, string $subject, string $message): void
    {
        if (is_array($recipient)) {
            $recipient = join(', ', $recipient);
        }

        $this->logger->info(
            'Sent notification to {recipient}: {subject}',
            [
                'recipient' => $recipient,
                'subject' => $subject
            ]
            );
    }
}
