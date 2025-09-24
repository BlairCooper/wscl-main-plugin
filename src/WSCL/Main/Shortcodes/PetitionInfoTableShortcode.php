<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Formidable\Formidable;
use RCS\WP\Shortcodes\ShortcodeBase;
use WSCL\Main\Petitions\PetitionsHelper;

class PetitionInfoTableShortcode extends ShortcodeBase
{
    const WHITE = 'white';
    const GRAY = '#f3f3f3';
    const BACKGROUND = '#EEEEEE';

    public function __construct(PluginInfoInterface $pluginInfo)
    {
        parent::__construct($pluginInfo, 'wscl-petition-info-table');
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        $attrs = shortcode_atts(
            [
                'id' => null
            ],
            $attrs,
            $this->getTagName()
            );

        ob_start();

        if (isset($attrs['id'])) {
            $entry = \FrmEntry::getOne($attrs['id'], true);
            if (isset($entry)) {
                $rows = [
                    [self::WHITE, 'Rider Name', $this->getValue($entry, PetitionsHelper::FLD_PR_RIDER_NAME)],
                    [self::GRAY, 'Rider Gender', $this->getValue($entry, PetitionsHelper::FLD_PR_RIDER_GENDER)],
                    [self::WHITE, 'Rider Grade', $this->getValue($entry, PetitionsHelper::FLD_PR_RIDER_GRADE)],
                    [self::GRAY, 'Rider Email', $this->getValue($entry, PetitionsHelper::FLD_PR_RIDER_EMAIL)],
                    [self::WHITE, 'Rider Phone', $this->getValue($entry, PetitionsHelper::FLD_PR_RIDER_PHONE)],
                    [self::GRAY, 'Team', $this->getValue($entry, PetitionsHelper::FLD_PR_TEAM_NAME)],
                    [self::WHITE, 'Coach Name', $this->getValue($entry, PetitionsHelper::FLD_PR_COACH_NAME)],
                    [self::GRAY, 'Coach Email', $this->getValue($entry, PetitionsHelper::FLD_PR_COACH_EMAIL)],
                    [self::WHITE, 'Current Category', $this->getValue($entry, PetitionsHelper::FLD_PR_CURRENT_CATEGORY)],
                    [self::GRAY, 'Requested Category', $this->getValue($entry, PetitionsHelper::FLD_PR_REQUESTED_CATEGORY)],
                    [self::WHITE, 'Results', $this->getValue($entry, PetitionsHelper::FLD_PR_RACE_RESULTS)],
                    [self::GRAY, 'Reason/Circumstance', $this->getValue($entry, PetitionsHelper::FLD_PR_REASONING)],
                    [self::WHITE, 'Attachments', $this->getAttachmentUrls($entry)],
                    [self::GRAY, 'Contact Name', $this->getValue($entry, PetitionsHelper::FLD_PR_CONTACT_NAME)],
                    [self::WHITE, 'Contact Relation', $this->getValue($entry, PetitionsHelper::FLD_PR_CONTACT_RELATION)],
                    [self::GRAY, 'Contact Email', $this->getValue($entry, PetitionsHelper::FLD_PR_CONTACT_EMAIL)],
                    [self::WHITE, 'Contact Phone', $this->getValue($entry, PetitionsHelper::FLD_PR_CONTACT_PHONE)]
                ];
            ?>
    <table style="background-color: <?php echo self::WHITE; ?>; width: 100%;">
    <tbody>
    <tr>
    <td style="text-align: center;">
        <table style="margin: auto; width: 500px; padding: 5px; border-spacing: 0;
            background-color: <?php echo self::BACKGROUND; ?>;">
            <tbody>
                <tr>
                <td style="
                    font-size: 16px;
                    vertical-align: middle;
                    color: #f9922b;
                    background-color: <?php echo self::BACKGROUND;?>;
                    padding: 10px;
                    text-align: start;
                    border: none;
                    "
                    colspan="2"
                    ><strong>Petition for Category Change Form</strong></td>
                </tr>
                <?php
                    foreach($rows as $row) {
                        echo $this->generateRow($row[0], $row[1], $row[2]);
                    }
                ?>
            </tbody>
        </table>
    </td>
    </tr>
    </table>
    <?php
            }
        }

        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    private function getValue(\stdClass $entry, string $field): string
    {
        $value = $entry->metas[Formidable::getFieldId($field)] ?? '';
        if (is_array($value)) {
            $value = join(' ', $value);
        }

        return $value;
    }

    private function getAttachmentUrls(\stdClass $entry): string
    {
        $urls = [];
        $uploadIds = $entry->metas[Formidable::getFieldId(PetitionsHelper::FLD_PR_UPLOADS)] ?? [];

        foreach($uploadIds as $uploadId) {
            $urls[]= wp_get_attachment_url($uploadId);
        }

        return join(', ', $urls);
    }

    private function generateRow(string $bgColor, string $label, string $value): string
    {
        ob_start();
        ?>
        <tr style="background-color: <?php echo $bgColor?>;">
            <td style="padding: 5px; width: 150px; vertical-align: top;"><?php echo $label;?></td>
            <td style="padding: 5px; width: auto; border: none;"><?php echo $value;?></td>
        </tr>
        <?php
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }
}
