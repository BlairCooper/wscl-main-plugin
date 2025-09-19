<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Types;

/**
 * Implements a mapping of group name to MailerLite Group Id.
 *
 * @implements \Iterator<string, int>
 */
class GroupIdMap implements \Iterator
{
    private const END_OF_LIST = 'eol';

    /** @var array<string, int> */
    private array $groupIdMap = array();

    private string $currentGroupName;

    public function put(string $groupName, int $groupId): void
    {
        $this->groupIdMap[$groupName] = $groupId;
    }

    public function get(string $groupName): ?int
    {
        return $this->groupIdMap[$groupName] ?? null;
    }

    public function count(): int
    {
        return count($this->groupIdMap);
    }

    public function containsGroup(string $groupName): bool
    {
        return array_key_exists($groupName, $this->groupIdMap);
    }

    public function remove(string $groupName): ?int
    {
        $group = $this->get($groupName);
        unset($this->groupIdMap[$groupName]);

        return $group;
    }

    public function isEmpty(): bool
    {
        return empty($this->groupIdMap);
    }

    //***********************************************************************
    //  Iterator functions
    //***********************************************************************
    public function current(): mixed
    {
//        return $this->groupIdMap[$this->key()] ?? false;

        return $this->groupIdMap[$this->getCurrentGroupName()];
    }

    public function key(): string
    {
        return $this->getCurrentGroupName();
    }

    public function next(): void
    {
        $grabNext = false;
        $newKey = null;

        foreach($this->groupIdMap as $key => $id) {
            if ($key == $this->getCurrentGroupName()) {
                $grabNext = true;
            } elseif ($grabNext) {
                $newKey = $key;
                break;
            }
        }

        $this->currentGroupName = is_null($newKey) ? self::END_OF_LIST : $newKey;
    }

    public function rewind(): void
    {
//        $this->currentGroupId = reset($this->groupIdMap);
    }

    public function valid(): bool
    {
        return self::END_OF_LIST != $this->getCurrentGroupName();
    }

    private function getCurrentGroupName(): string
    {
        if (!isset($this->currentGroupName)) {
            $keys = array_keys($this->groupIdMap);
            $this->currentGroupName = array_shift($keys);
        }

        return $this->currentGroupName;
    }
}
