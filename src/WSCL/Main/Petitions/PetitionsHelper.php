<?php
declare(strict_types = 1);
namespace WSCL\Main\Petitions;

use RCS\WP\PluginInfoInterface;
use RCS\WP\UrlDataObject;
use RCS\WP\Formidable\Formidable;

class PetitionsHelper
{
    private const APPROVAL_TRIGGER = 1817;
    private const DENIAL_TRIGGER = 1816;

    const FORM_PETITION_REQUEST = 'wscl_petition';

    const FLD_PR_RIDER_NAME = 'pr_rider_name';
    const FLD_PR_RIDER_GENDER = 'pr_rider_gender';
    const FLD_PR_RIDER_GRADE = 'pr_rider_grade';
    const FLD_PR_RIDER_EMAIL = 'pr_rider_email';
    const FLD_PR_RIDER_PHONE = 'pr_rider_phone';
    const FLD_PR_TEAM_NAME = 'pr_team_name';
    const FLD_PR_COACH_NAME = 'pr_coach_name';
    const FLD_PR_COACH_EMAIL = 'pr_coach_email';
    const FLD_PR_CURRENT_CATEGORY = 'pr_curr_category';
    const FLD_PR_REQUESTED_CATEGORY = 'pr_req_category';
    const FLD_PR_RACE_RESULTS = 'pr_race_results';
    const FLD_PR_REASONING = 'pr_reasoning';
    const FLD_PR_UPLOADS = 'pr_uploads';
    const FLD_PR_CONTACT_NAME = 'pr_contact_name';
    const FLD_PR_CONTACT_RELATION = 'pr_contact_relationship';
    const FLD_PR_CONTACT_EMAIL = 'pr_contact_email';
    const FLD_PR_CONTACT_PHONE = 'pr_contact_phone';
    const FLD_PR_DETERMINATION = 'pr_determination';
    const FLD_PR_CAPTCHA = 'pr_captcha';

    const VAL_DETERMINATION_PENDING = 'Pending';
    const VAL_DETERMINATION_APPROVED = 'Approved';
    const VAL_DETERMINATION_DENIED = 'Denied';

    const AJAX_TAG = 'wscl_petition';
    const AJAX_ACTION_PETITION_APPROVAL = 'petitionApproval';

    const AJAX_DATA_ARG = 'id';
    const AJAX_ENTRY_ID = 'entryId';
    const AJAX_DECISION = 'decision';

    private PluginInfoInterface $pluginInfo;

    public function __construct(PluginInfoInterface $pluginInfo)
    {
        $this->pluginInfo = $pluginInfo;

        add_action('frm_trigger_email_action', [$this, 'frmTriggerEmailAction'], 30, 2);
        add_action('frm_after_update_entry', [$this, 'frmAfterUpdateEntry'], 20, 2);

        add_filter('frm_skip_form_action', [$this, 'frmSkipFormAction'], 20, 2);

        add_action('wp_enqueue_scripts', [$this, 'wpEnqueueScripts'], 20);
        add_action('wp_ajax_'.self::AJAX_ACTION_PETITION_APPROVAL, [$this, 'ajaxHandlePetitionApproval']);
    }

    /**
     *
     * @param bool $skipThisAction
     * @param array<string, mixed> $args
     *
     * @return bool
     */
    public function frmSkipFormAction(bool $skipThisAction, array $args): bool
    {
        if (!$skipThisAction && in_array($args['action']->ID, [self::APPROVAL_TRIGGER, self::DENIAL_TRIGGER])) {
            $entryId =  $args['entry'];

            if (is_object($args['entry'])) {
                $entryId = intval($args['entry']->id);
            } else {
                $entryId = intval($entryId);    // likely currently a string
            }

            /* @var PetitionNotifications */
            $notifications = PetitionNotifications::init();

            if ($args['action']->ID == self::APPROVAL_TRIGGER) {
                $previouslyTriggered = $notifications->wasApprovalTriggered($entryId);
            } else {
                $previouslyTriggered = $notifications->wasDenialTriggered($entryId);
            }

            if ($previouslyTriggered) {
                $skipThisAction = true;
            }
        }

        return $skipThisAction;
    }

    public function frmTriggerEmailAction(mixed $action, \stdClass $entry): void
    {
        if ($action->ID == self::APPROVAL_TRIGGER || $action->ID == self::DENIAL_TRIGGER) {
            /* @var PetitionNotifications */
            $notifications = PetitionNotifications::init();

            if ($action->ID == self::APPROVAL_TRIGGER) {
                $notifications->approvalTriggered(intval($entry->id));
            } else {
                $notifications->denialTriggered(intval($entry->id));
            }
        }
    }

    public function frmAfterUpdateEntry(int $entryId, int $formId): void
    {
        if ($formId == Formidable::getFormId(self::FORM_PETITION_REQUEST)) {
            $value = \FrmEntryMeta::get_entry_meta_by_field(
                $entryId,
                Formidable::getFieldId(self::FLD_PR_DETERMINATION)
                );

            if (isset($value) && self::VAL_DETERMINATION_PENDING == $value) {
                $notifications = PetitionNotifications::init();

                $notifications->reset($entryId);
            }
        }
    }

    public function wpEnqueueScripts(): void
    {
        if (is_user_logged_in()) {
            wp_enqueue_script(
                $this->pluginInfo->getSlug(),
                $this->pluginInfo->getUrl() . 'scripts/wscl-petition.js',
                array('jquery'),
                $this->pluginInfo->getVersion(),
                false
                );
        }
    }

    public static function createUrlDataObject(int $entryId, string $decision): UrlDataObject
    {
        return (new UrlDataObject())
           ->add([
               self::AJAX_ENTRY_ID => strval($entryId),
               self::AJAX_DECISION => $decision
            ]
        );
    }

    public function ajaxHandlePetitionApproval(): void
    {
        if (isset($_REQUEST[self::AJAX_DATA_ARG])) {
            $urlData = $_REQUEST[self::AJAX_DATA_ARG];

            $urlDataObj = new UrlDataObject($urlData);
            $entryId = $urlDataObj->get(self::AJAX_ENTRY_ID);
            $decision = $urlDataObj->get(self::AJAX_DECISION);

            if (isset($entryId) &&
                isset($decision) &&
                in_array($decision, [self::VAL_DETERMINATION_APPROVED, self::VAL_DETERMINATION_DENIED]))
            {
                \FrmEntryMeta::update_entry_meta($entryId, Formidable::getFieldId(self::FLD_PR_DETERMINATION), null, $decision);

                do_action('frm_after_update_entry', $entryId, Formidable::getFormId(self::FORM_PETITION_REQUEST));
            }
        }

        wp_die(); // All ajax handlers die when finished
    }
}
