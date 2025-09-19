<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

class SortedArray
{
    /** @var array<mixed> */
    private array $entries = array();
    /** @var callable */
    private $compareFunc;

    public function __construct(callable $compareFunc)
    {
        $this->compareFunc = $compareFunc;
    }

    public function add(mixed $entry): void
    {
        array_push($this->entries, $entry);
    }

    /**
     *
     * @return array<mixed>
     */
    public function getEntries(): array
    {
        usort($this->entries, $this->compareFunc);

        return $this->entries;
    }
}

