<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;

class ResultsDetailsRaceResultsShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName(): string
    {
        return 'wscl-results-details-race-results';
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs= [], $content = ''): string
    {
        ob_start();

        $raceResultId = $_POST['__raceResultId'] ?? null;

        if (isset($raceResultId)) {
            ?>
        <div id="divRRPublish" class="RRPublish"></div>
        <script type="text/javascript" src="https://my.raceresult.com/RRPublish/load.js.php?lang=en"></script>
        <script type="text/javascript">
        <!--
        var rrp=new RRPublish(document.getElementById("divRRPublish"), <?php echo $raceResultId; ?>, "results");
        rrp.ShowTimerLogo=true;
        rrp.ShowInfoText=true;
        -->
        </script>
        <style>
        .TilesList td {
          min-width: 30px;
        }
        /* Add custom CSS here or elsewhere to change the design */
        </style>
        <?php
        }
        else {
            echo 'Missing RaceResult race Id';
        }

        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }
}
