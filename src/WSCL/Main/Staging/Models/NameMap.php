<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Models;

class NameMap
{
    private const WP_OPTION_NAME = 'wsclNameMappings';

    /** @var NameMapEntry[] */
    private array $mappings;

    private function __construct()
    {
        $this->mappings = array();
    }

    public static function getInstance(): NameMap
    {
        $inst = new NameMap();

        $inst->mappings = \get_option(self::WP_OPTION_NAME, []);

        return $inst;
    }

    private function save(): void
    {
        \update_option(self::WP_OPTION_NAME, $this->mappings);
    }

    /**
     *
     * @return NameMapEntry[] Array of NameMapEntry
     */
    public function getAll(): array
    {
        return $this->mappings;
    }

    public function add(NameMapEntry $entry): NameMapEntry
    {
        // If there is no ID or the entry for the ID doesn't already exist, add the entry
        if (empty($entry->getId()) || empty($this->findById($entry->getId()))) {
            $entry->setId($this->fetchNextId());
            array_push($this->mappings, $entry);

            $this->save();
        } else {
            // Update the entry by passing it to the update() method
            $entry = $this->update($entry);
        }

        return $entry;
    }

    public function update(NameMapEntry $entry): ?NameMapEntry
    {
        $result = null;

        $mapEntry = $this->findById($entry->getId());
        if (isset($mapEntry)) {
            $mapEntry->update($entry);

            $this->save();

            $result = $entry;
        }

        return $result;
    }

    public function delete(int $entryId): ?NameMapEntry
    {
        $mapEntry = $this->findById($entryId);

        if (isset($mapEntry)) {
            $this->mappings =
                array_values(   // reindex array to ensure a 0 offset
                    array_filter(
                        $this->mappings,
                        fn(NameMapEntry $n) => $n->getId() != $mapEntry->getId()
                        )
                );

            $this->save();
        }

        return $mapEntry;
    }

    private function fetchNextId(): int
    {
        $result = 0;

        foreach ($this->mappings as $entry) {
            $result = max($entry->getId(), $result);
        }

        return $result + 1;
    }

    /**
     * Find an entry for a mapping by entry identifier
     *
     * @param int $id An entry identifier
     *
     * @return NameMapEntry|NULL The entry for the identifier or null if
     *      there isn't a mapping.
     */
    public function findById(int $id): ?NameMapEntry
    {
        $result = null;

        $matches = array_filter($this->mappings, fn(NameMapEntry $n) => $n->getId() == $id);

        if (!empty($matches)) {
            $result = array_shift($matches);
        }

        return $result;
    }

    /**
     * Find an entry for a mapping by type.
     *
     * @param string $type The type of mapping
     *
     * @return NameMapEntry[] An array of NameMapEntry, of the requested type.
     */
    public function findByType(string $type): array
    {
        return array_filter($this->mappings, fn(NameMapEntry $n) => $n->getType() == $type);
    }

    /**
     * Find an entry for a mapping by  name.
     *
     * @param string $name A  Name
     *
     * @return NameMapEntry|NULL The entry for the  name or null if there
     *      isn't a mapping.
     */
    public function findByName(string $type, string $name): ?NameMapEntry
    {
        /** @var ?NameMapEntry */
        $result = null;

        /** @var NameMapEntry[] */
        $matches = array_filter(
            $this->mappings,
            fn(NameMapEntry $n) => $n->getType() == $type && $n->getInName() == $name
            );

        if (!empty($matches)) {
            $result = array_shift($matches);
        }

        return $result;
    }

    /**
     * Fetch the mapping for a  name.
     *
     * If there is no mapping, the input name is returned.
     *
     * @param string $name A  name
     *
     * @return string The mapped  name, or the original if there isn't a
     *      mapping.
     */
    public function getMappedName(string $type, string $name): string
    {
        /** @var string */
        $result = $name;

        /** @var NameMapEntry[] */
        $matches = array_filter(
            $this->mappings,
            fn(NameMapEntry $n) => $n->getType() == $type && $n->getInName() == $name
            );

        if (!empty($matches)) {
            /** @var NameMapEntry */
            $entry = array_shift($matches);
            $result = $entry->getOutName();
        }

        return $result;
    }

}
