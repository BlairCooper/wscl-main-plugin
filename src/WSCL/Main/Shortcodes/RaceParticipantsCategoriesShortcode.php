<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;

class RaceParticipantsCategoriesShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName(): string
    {
        return 'wscl-race-participants-categories';
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        $result = '';

        $attrs = shortcode_atts(array(
            'raceresultid' => null
        ), $attrs);

        ob_start();

        $raceResultId = $attrs['raceresultid'];

        if (isset($raceResultId)) {
            ?>
        <div id="divRRPublish" class="RRPublish"></div>
        <script type="text/javascript" src="https://my.raceresult.com/RRPublish/load.js.php?lang=en"></script>
        <script type="text/javascript">
        <!--
          var rrp=new RRPublish(document.getElementById("divRRPublish"), <?php echo $raceResultId; ?>, "participants");
          rrp.ShowTimerLogo=false;
          rrp.ShowInfoText=true;
        -->
        </script>
        <style>
          /* Add custom CSS here or elsewhere to change the design */
        #divRRPublish {
          border: 1px solid black;
        }
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
