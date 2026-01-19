<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;

class ResultsRaceShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName():string
    {
        return 'wscl-results-race';
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs= [], $content = ''): string
    {
        global $wsclRaceEntry;

        $attrs = shortcode_atts(array(
            'raceresultid' => null,
            'title' => 'Race Results'
            ),
            $attrs,
            static::getTagName()
            );

        return $this->renderResultsButton(intval($wsclRaceEntry['number']), $wsclRaceEntry['name'], $wsclRaceEntry['date'], intval($attrs['raceresultid']), $attrs['title']);
    }

    public function filterAttributes(array $combinedAtts, array $defaultPairs, array $providedAtts, string $shortcode): array
    {
        $combinedAtts['raceresultid'] = intval($combinedAtts['raceresultid']);

        return $combinedAtts;
    }

    protected function renderResultsButton(int $raceNumber, string $name, string $date, ?int $raceResultId, ?string $title): string
    {
        $html = '';

        $targetPage = get_page_by_path('/race-results/race-result-details');
        $targetUrl = get_page_link($targetPage);

        if (isset($raceResultId)) {
            ob_start();
            $formId = "race_result_form_$raceResultId";
            $buttonId = "race_result_button_$raceResultId";

            ?>
            <form id="<?php echo $formId;?>"
              target="_blank"
              action="<?php echo $targetUrl ?>"
              method="post"
              enctype="application/x-www-form-urlencoded">
                  <input type="hidden" name="__raceNum" type="hidden" value="<?php echo $raceNumber; ?>">
                  <input type="hidden" name="__raceName" type="hidden" value="<?php echo $name; ?>">
                  <input type="hidden" name="__raceDate" type="hidden" value="<?php echo $date; ?>">
                  <input type="hidden" name="__raceResultId" type="hidden" value="<?php echo $raceResultId; ?>">
                  <input type="hidden" name="__raceTitle" type="hidden" value="<?php echo $title; ?>">
            </form>

            <?php
            echo do_shortcode('[av_button id="'.$buttonId.'" label="'.$title.'" link="javascript:()" size="small" position="center" custom_class="wsclResultsButton" color="theme-color-highlight"]');

            $html = ob_get_contents();
            ob_end_clean();

            add_action('wp_footer', function () use ($buttonId, $formId) {
                ?>
                <script>
                    jQuery(window).load(function() {
                        // Button onClick event
                        jQuery("#<?php echo $buttonId ?> a").on("click", function() {
                            document.getElementById('<?php echo $formId; ?>').submit(); return false;
                        });
                    });
                </script>
                <?php
            } );
        }

        return $html;
    }

}
