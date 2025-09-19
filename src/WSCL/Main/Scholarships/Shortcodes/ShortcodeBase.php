<?php declare(strict_types = 1);
namespace WSCL\Main\Scholarships\Shortcodes;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Formidable\Formidable;
use WSCL\Main\Scholarships\ScholarshipOptionsInterface;
use WSCL\Main\Scholarships\ScholarshipsHelper;

abstract class ShortcodeBase extends \RCS\WP\Shortcodes\ShortcodeBase
{
    private ?\stdClass $frmEntry;

    protected function __construct(
        PluginInfoInterface $pluginInfo,
        string $tagName,
        protected ScholarshipOptionsInterface $options
        )
    {
        parent::__construct($pluginInfo, $tagName);
    }

    /**
     *
     * @param array<int, mixed> $metas
     *
     * @return string
     */
    abstract protected function privRenderShortcode(array $metas): string;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        $result = '';

        $attrs = shortcode_atts(array(
            'entry' => null
        ), $attrs);

        if (isset($attrs['entry']) ) {
            $this->frmEntry = \FrmEntry::getOne($attrs['entry'], true);

            if (isset($this->frmEntry)) {
                $result = $this->privRenderShortcode($this->frmEntry->metas);
            }
        }

        return $result;
    }

    protected function isForLeagueFee(): bool
    {
        return ScholarshipsHelper::LEAGUE_FEE == $this->getWhatFor();
    }

    protected function getWhatFor(): string
    {
        if ($this->isCoachApplication()) {
            $fieldId = Formidable::getFieldId(ScholarshipsHelper::FLD_COACH_WHAT_FOR);
        } else {
            $fieldId = Formidable::getFieldId(ScholarshipsHelper::FLD_STUDENT_WHAT_FOR);
        }

        return $this->frmEntry->metas[$fieldId];
    }

    protected function isCoachApplication(): bool
    {
        return Formidable::getFormId(ScholarshipsHelper::FORM_COACH_SCHOLARSHIP) == $this->frmEntry->form_id;
    }

    protected function getCost(): int
    {
        $result = 0;

        if ($this->isCoachApplication()) {
            if ($this->isForLeagueFee()) {
                $result = $this->options->getCoachFee();
            } else {
                $result = $this->frmEntry->metas[Formidable::getFieldId(ScholarshipsHelper::FLD_COACH_CLINIC_COST)];
            }
        } else {
            if ($this->isForLeagueFee()) {
                if (ScholarshipsHelper::isSpringSeason()) {
                    $result = $this->options->getSpringSeasonFee();
                } else {
                    $result = $this->options->getFallSeasonFee();
                }
            } else {
                $result = $this->frmEntry->metas[Formidable::getFieldId(ScholarshipsHelper::FLD_STUDENT_CLINIC_COST)];
            }
        }

        return $result;
    }

    protected function getAward(): int
    {
        if ($this->isCoachApplication()) {
            $fieldId = Formidable::getFieldId(ScholarshipsHelper::FLD_COACH_AWARD_AMOUNT);
        } else {
            $fieldId = Formidable::getFieldId(ScholarshipsHelper::FLD_STUDENT_AWARD_AMOUNT);
        }

        return intval($this->frmEntry->metas[$fieldId]);
    }
}
