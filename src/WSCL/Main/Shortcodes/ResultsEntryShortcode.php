<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;

class ResultsEntryShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName(): string
    {
        return 'wscl-results-entry';
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs= [], $content = ''): string
    {
        global $wsclRaceEntry;

        $result = '';

        $wsclRaceEntry = shortcode_atts(array(
            'number' => 0,
            'name' => 'not specified',
            'date' => 'January 1, 1970'
            ),
            $attrs,
            static::getTagName()
            );

        ob_start();

        ?>
        <div class="wsclResultsEntry">
          <div class="wsclResultsEvent">
            Race <?php echo $wsclRaceEntry['number']; ?><br>
            <?php echo $wsclRaceEntry['name']; ?><br>
            <?php echo $wsclRaceEntry['date']; ?>
          </div>
          <div class="wsclResultsButtons">
            <?php
                $html = do_shortcode( shortcode_unautop( $content ) );
                $html = preg_replace('/\<br ?\/?\>/i', '', $html);
                echo $html;
            ?>
          </div>
        </div>
        <?php

        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    public function filterAttributes(array $combinedAtts, array $defaultPairs, array $providedAtts, string $shortcode): array
    {
        $combinedAtts['number'] = intval($combinedAtts['number']);

        return $combinedAtts;
    }

}
