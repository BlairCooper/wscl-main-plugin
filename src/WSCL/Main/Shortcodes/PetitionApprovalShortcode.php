<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Formidable\Formidable;
use RCS\WP\Shortcodes\ShortcodeBase;
use WSCL\Main\Petitions\PetitionsHelper;

class PetitionApprovalShortcode extends ShortcodeBase
{
    public const PARAM_ID = 'id';

    public function __construct(PluginInfoInterface $pluginInfo)
    {
        parent::__construct($pluginInfo, 'wscl-petition-buttons');
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        $result = '';
        $attrs = shortcode_atts(
            [
                self::PARAM_ID => null
            ],
            $attrs,
            $this->getTagName()
            );

        if (isset($attrs[self::PARAM_ID])) {
            $state = \FrmEntryMeta::get_entry_meta_by_field(
                $attrs[self::PARAM_ID],
                Formidable::getFieldId(PetitionsHelper::FLD_PR_DETERMINATION)
                );

            if (isset($state)) {
                $result = $this->generateContent($state, intval($attrs[self::PARAM_ID]));
            }
        }

        return $result;
    }

    private function generateContent(string $state, int $entryId): string
    {
        ob_start();

        echo "<div class='petitionDetermination' data-event_id='$entryId'>";

        switch ($state) {
            case PetitionsHelper::VAL_DETERMINATION_APPROVED:
                echo $this->generateApprovalButton($state, '', $entryId, 'green');
                break;
            case PetitionsHelper::VAL_DETERMINATION_DENIED:
                echo $this->generateApprovalButton($state, '', $entryId, 'red');
                break;
            case PetitionsHelper::VAL_DETERMINATION_PENDING:
            default:
                echo $this->generateApprovalButton('Approve', PetitionsHelper::VAL_DETERMINATION_APPROVED, $entryId, 'theme-color');
                echo $this->generateApprovalButton('Deny', PetitionsHelper::VAL_DETERMINATION_DENIED, $entryId, 'theme-color');
                break;

        }

        echo '</div>';

        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    private function generateApprovalButton(string $label, string $decision, int $entryId, string $color): string
    {
        $nonce = wp_create_nonce($decision . $entryId);

        $buttonAttrs = [
            'label' => $label,
            'link'          => 'manually,#" onclick="return false;',
            'link_dynamic'          => 'manually,#" onclick="return false;',
            'size'          => 'small',
            'position'      => 'center',
            'color'         => $color,
            'custom_font'   => '#ffffff',
        ];

        $spanAttrs = [
            'data-id'       => PetitionsHelper::createUrlDataObject($entryId, $decision)->encode(),
            'data-action'   => !empty($decision) ? PetitionsHelper::AJAX_ACTION_PETITION_APPROVAL : '',
            'data-nonce'    => $nonce,
            'class'         => 'petitionButton'
            ];

        $html = sprintf(
            '<span %s>[av_button %s]</span>',
            join(
                ' ',
                array_map(
                    fn(string $key, string $value): string => sprintf('%s="%s"', $key, $value),
                    array_keys($spanAttrs),
                    array_values($spanAttrs)
                    )
                ),
            join(
                ' ',
                array_map(
                    fn(string $key, string $value): string => sprintf('%s="%s"', $key, $value),
                    array_keys($buttonAttrs),
                    array_values($buttonAttrs)
                    )
                )
            );

        return do_shortcode($html);
    }
}
