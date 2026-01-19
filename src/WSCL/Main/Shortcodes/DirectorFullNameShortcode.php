<?php
declare(strict_types = 1);
namespace WSCL\Main\Shortcodes;

use RCS\WP\Shortcodes\ShortcodeImplInf;
use RCS\WP\Shortcodes\ShortcodeImplTrait;
use WSCL\Main\WsclMainOptionsInterface;

class DirectorFullNameShortcode implements ShortcodeImplInf
{
    use ShortcodeImplTrait;

    public function __construct(
        private WsclMainOptionsInterface $options
        )
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public static function getTagName(): string
    {
        return 'wscl-ed-fullname';
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        return $this->options->getDirectorFullName();
    }
}
