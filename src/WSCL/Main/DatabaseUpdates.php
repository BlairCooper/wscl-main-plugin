<?php
declare(strict_types = 1);
namespace WSCL\Main;

use RCS\WP\Database\DatabaseUpdatesInterface;
use WSCL\Main\Staging\Models\NameMap;
use WSCL\Main\Staging\Models\NameMapEntry;

class DatabaseUpdates implements DatabaseUpdatesInterface
{
    public function __construct(
//        private BgProcessInterface $bgProcess
        )
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Database\DatabaseUpdatesInterface::getDatabaseUpgrades()
     */
    public function getDatabaseUpgrades(): array
    {
        // Disable Formidable trying to inject set cookie code for entries we create during the upgrade
        remove_action('frm_after_create_entry', 'FrmProEntriesController::maybe_set_cookie', 20);

        return [
            '2.0.1' => function () { $this->upgradeDatabaseTo_2_0_1(); },
//             '2.0.40' => function () { $this->upgradeDatabaseTo_2_0_40(); },
//             '2.0.32' => function () { $this->upgradeDatabaseTo_2_0_32(); },
//             '2.0.24' => function () { $this->upgradeDatabaseTo_2_0_24(); },
//             '2.0.20' => function () { $this->upgradeDatabaseTo_2_0_20(); },
            ];
    }

    private function upgradeDatabaseTo_2_0_1(): void
    {
        // Create an alias to the new namespace from the old namespace
        class_alias('WSCL\Main\Staging\Models\NameMapEntry', 'WSCL\WordPress\Staging\Models\NameMapEntry');

        // To force WordPress to save the entries with the new namespace we
        // need to make it so the contents of the map changes.
        $map = NameMap::getInstance();
        $tmpEntry = NameMapEntry::getClassFactory()((object)[
            'type'    => 'tmp',
            'inName'  => 'A',
            'outName' => 'B'
        ]);

        $map->add($tmpEntry);
        $map->delete($tmpEntry->getId());
    }
}
