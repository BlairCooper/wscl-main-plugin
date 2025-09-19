<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\PluginInfoInterface;
use RCS\WP\Shortcodes\ShortcodeBase;

class RaceResultRegistrationShortcode extends ShortcodeBase
{
    private const EVENT_ID = 'id';
    private const EVENT_NAME = 'name';
    private const EVENT_KEY = 'key';
    private const EVENT_SERVER = 'server';

    private const DEFAULT_EVENT_SERVER = 'https://events2.raceresult.com';

    public function __construct(PluginInfoInterface $pluginInfo)
    {
        parent::__construct($pluginInfo, 'wscl-race-result-registration');
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        $result = '';
        $errors = [];

        $attrs = shortcode_atts(array(
            self::EVENT_ID => null,
            self::EVENT_NAME => null,
            self::EVENT_KEY => null,
            self::EVENT_SERVER => self::DEFAULT_EVENT_SERVER
        ), $attrs);

        foreach ([self::EVENT_ID, self::EVENT_NAME, self::EVENT_KEY] as $field) {
            if (!isset($attrs[$field])) {
                $errors[] = sprintf(
                'No "%s" parameter specified for %s shortcode',
                $field,
                $this->getTagName()
                );
            }
        }

        if (empty($errors)) {
            $result .= sprintf(
                '<script type="text/javascript">var RRReg_eventid="%s", RRReg_name="%s", RRReg_key="%s", RRReg_server="%s";</script>',
                $attrs[self::EVENT_ID],
                $attrs[self::EVENT_NAME],
                $attrs[self::EVENT_KEY],
                $attrs[self::EVENT_SERVER]
                );
            $result .= sprintf(
                '<script type="text/javascript" src="%s/registrations/init.js?lang=en"></script>',
                $attrs[self::EVENT_SERVER]
                );
            $result .= '<style>.RRReg div.RRReg_EntryField {padding: 20px 0 0 0; } .RRReg button {min-width: 100px; min-height: 30px;}</style>';

        } else {
            array_unshift($errors, '<strong>Missing required attributes for the "'. $this->getTagName().'" shortcode</strong>');
            array_push($errors, '');

            $result = join('<br>', $errors);
        }

        return $result;
    }
}
