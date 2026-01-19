<?php
declare(strict_types=1);
namespace WSCL\Main\Maps;

use RCS\WP\Shortcodes\ScriptMeta;
use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;
use RCS\WP\PluginInfoInterface;
use WSCL\Main\WsclMainOptionsInterface;

abstract class WsclMapShortcodeBase implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    const COORDINATE_REGEX = '/^ *(-?1?[0-7]?[0-9]\.[0-9]{6,}) *, *(-?1?[0-7]?[0-9]\.[0-9]{6,}) *$/';
    const HTML_BREAK = '<br/>';

    protected function __construct(
        protected PluginInfoInterface $pluginInfo,
        private WsclMainOptionsInterface $options
        )
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getScripts()
     */
    public function getScripts(): array
    {
        $apiKey = $this->options->getGoogleMapsApiKey();

        return [
            new ScriptMeta('GoogleMapsApi', "https://maps.googleapis.com/maps/api/js?key=$apiKey", [], 'async'),
            new ScriptMeta('wsclMaps', $this->pluginInfo->getUrl() . 'scripts/wscl-maps.js', ['jquery', 'GoogleMapsApi'], 'defer')
            ];
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode($attrs = [], $content = null): string
    {
        $attrs = shortcode_atts(array(
            'width' => '100%',
            'height' => '650px',
            'background-color' => 'grey'
            ),
            $attrs,
            static::getTagName()
        );

        $divId = uniqid('wsclMaps');
        $mapDataVar = $divId.'_data';

        wp_localize_script('wsclMaps', $mapDataVar, $this->renderGoogleMarkersJavascript());

        $html = PHP_EOL;
        $html .= $this->getStyleElement($divId, $attrs);
        $html .= PHP_EOL;
        $html .= $this->getScriptElement($divId, $mapDataVar);
        $html .= PHP_EOL;
        $html .= '<div id=outer'.$divId.'><div id="'.$divId.'"></div></div>';
        $html .= PHP_EOL;

        return $html;
    }

    /**
     *
     * @param string $divId
     * @param array<string, string> $attrs
     *
     * @return string
     */
    protected function getStyleElement(string $divId, array $attrs): string {
        $html = '';

        $html .= '<STYLE>'.PHP_EOL;
        $html .= "#$divId { ";
        foreach ($attrs as $key => $value) {
            $html .= $key.': '.$value.'; ';
        }
        $html .= '}'.PHP_EOL;
        $html .= "#$divId .info-window * { color: #222222; } ".PHP_EOL;
        $html .= "#$divId .info-window a { color: #ee7722; text-decoration: underline; } ".PHP_EOL;
        $html .= "#$divId .info-window h5 { font-weight: bold; } ".PHP_EOL;
        $html .= '</STYLE>';

        return $html;
    }

    protected function getScriptElement(string $divId, string $mapDataVar): string {
        return <<<END_OF_SCRIPT
        <SCRIPT>
            jQuery(document).ready(function($) {    //wrapper
                initMap("$divId", $mapDataVar);
            });
        </SCRIPT>
        END_OF_SCRIPT;
    }

    /**
     *
     * @param string $coordStr
     *
     * @return float[]|NULL
     */
    protected function parseCoordinateString(?string $coordStr): ?array {
        $result = null;

        if (!is_null($coordStr)) {
            $matches = array();

            if (preg_match(self::COORDINATE_REGEX, $coordStr, $matches)) {
                $result = array(floatval($matches[1]), floatval($matches[2]));
            }
        }

        return $result;
    }

    protected function formatInfoWindowConent(string $title, string $body): string {
        return <<<END_OF_CONTENT
            <div class=info-window >
                <div>
                </div>
                <h5 class=firstHeading>$title</h5>
                <div >
                    <p>$body</p>
                </div>
            </div>
        END_OF_CONTENT;
    }

    /**
     *
     * @return LocationInfo[] Array of LocationInfo instances.
     */
    abstract protected function renderGoogleMarkersJavascript(): array;
}
