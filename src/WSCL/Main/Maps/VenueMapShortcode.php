<?php
declare(strict_types = 1);
namespace WSCL\Main\Maps;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Formidable\Formidable;
use WSCL\Main\WsclMainOptionsInterface;

class VenueMapShortcode
    extends WsclMapShortcodeBase
{
    public function __construct(
        PluginInfoInterface $pluginInfo,
        WsclMainOptionsInterface $options
        )
    {
        parent::__construct($pluginInfo, $options, 'wscl_venue_map');
    }

    protected function renderGoogleMarkersJavascript(): array
    {
        $result = array();

        $entries = \FrmEntry::getAll(
            array ('it.form_id' => Formidable::getFormId('wscl_venue')),    // $where,
            '', // $order_by = '',
            '',     // $limit = '',
            true,   // $meta = false,
            false   // $inc_form = true
            );

        foreach ($entries as $entry) {
            if (!isset($entry->metas[Formidable::getFieldId('vl_inactive')])) {
                $locationInfo = new LocationInfo($entry->metas[Formidable::getFieldId('vl_name')]);
                $locationInfo->setWindowContent($this->formatInfoContent($entry->metas));

                $coordinates = $this->parseCoordinateString($entry->metas[Formidable::getFieldId('vl_coord')]);
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
        $venue = $metas[Formidable::getFieldId('vl_name')];

        $body = '';

        if (isset($metas[Formidable::getFieldId('vl_address')])) {
            $body .= $metas[Formidable::getFieldId('vl_address')] . self::HTML_BREAK;
        }

        $body .= sprintf(
            "%s, %s %s",
            $metas[Formidable::getFieldId('vl_city')],
            $metas[Formidable::getFieldId('vl_state')],
            $metas[Formidable::getFieldId('vl_zip')]
            )
            .self::HTML_BREAK;

        return $this->formatInfoWindowConent($venue, $body);
    }
}
