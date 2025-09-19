<?php
declare(strict_types = 1);
namespace WSCL\Main\Maps;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Formidable\Formidable;
use WSCL\Main\WsclMainOptionsInterface;

class TeamMapShortcode
    extends WsclMapShortcodeBase
{
    public function __construct(
        PluginInfoInterface $pluginInfo,
        WsclMainOptionsInterface $options
        )
    {
        parent::__construct($pluginInfo, $options, 'wscl_team_map');
    }

    protected function renderGoogleMarkersJavascript(): array
    {
        $result = array();

        $entries = \FrmEntry::getAll(
            array ('it.form_id' => Formidable::getFormId(TeamLocationForm::FORM_KEY)),    // $where,
            '', // $order_by = '',
            '',     // $limit = '',
            true,   // $meta = false,
            false   // $inc_form = true
            );

        foreach ($entries as $entry) {
            if (!isset($entry->metas[Formidable::getFieldId(TeamLocationForm::FLD_INACTIVE)])) {
                $locationInfo = new LocationInfo($entry->metas[Formidable::getFieldId(TeamLocationForm::FLD_NAME)]);
                $locationInfo->setWindowContent($this->formatInfoContent($entry->metas));

                $coordinates = $this->parseCoordinateString($entry->metas[Formidable::getFieldId(TeamLocationForm::FLD_COORD)]);
                if (!is_null($coordinates)) {
                    $locationInfo->setLatitude($coordinates[0]);
                    $locationInfo->setLongitude($coordinates[1]);
                    array_push($result, $locationInfo);
                }
            }
        }

        return $result;
    }

    /**
     *
     * @param array<int, mixed> $metas
     *
     * @return string
     */
    private function formatInfoContent(array $metas): string {
        $team = $metas[Formidable::getFieldId(TeamLocationForm::FLD_NAME)];

        $body = ''
            .sprintf(
                "%s, %s %s",
                $metas[Formidable::getFieldId(TeamLocationForm::FLD_CITY)] ?? '',
                $metas[Formidable::getFieldId(TeamLocationForm::FLD_STATE)] ?? '',
                $metas[Formidable::getFieldId(TeamLocationForm::FLD_ZIP)] ?? ''
                )
            .self::HTML_BREAK;

        if (isset($metas[Formidable::getFieldId(TeamLocationForm::FLD_EMAIL)])) {
            $body .= 'Email: <a href=mailto:'.$metas[Formidable::getFieldId(TeamLocationForm::FLD_EMAIL)].' target=_blank>'.$metas[Formidable::getFieldId(TeamLocationForm::FLD_EMAIL)].'</a>'
            .self::HTML_BREAK;
        }

        if (isset($metas[Formidable::getFieldId(TeamLocationForm::FLD_PHONE)])) {
            $body .= 'Phone: '.$metas[Formidable::getFieldId(TeamLocationForm::FLD_PHONE)].self::HTML_BREAK;
        }

        if (isset($metas[Formidable::getFieldId(TeamLocationForm::FLD_COACH)])) {
            $body .= sprintf(
                'Head Coach: %s %s',
                $metas[Formidable::getFieldId(TeamLocationForm::FLD_COACH)]['first'],
                $metas[Formidable::getFieldId(TeamLocationForm::FLD_COACH)]['last']
                )
            .self::HTML_BREAK;
        }

        if (isset($metas[Formidable::getFieldId(TeamLocationForm::FLD_REG_URL)])) {
            $body .= sprintf(
                '<a href=%s referrerpolicy=no-referrer target=_blank>Regisration Link</a>',
                $metas[Formidable::getFieldId(TeamLocationForm::FLD_REG_URL)]
                )
                .self::HTML_BREAK;
        }

        return $this->formatInfoWindowConent($team, $body);
    }
}
