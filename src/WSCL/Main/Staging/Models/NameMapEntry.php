<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Models;

use RCS\Traits\SerializeAsArrayTrait;

class NameMapEntry implements \JsonSerializable
{
    private int $id;
    private string $type;
    private string $inName;
    private string $outName;

    use SerializeAsArrayTrait;

    public static function getClassFactory() : callable
    {
        return static function (\stdClass $stdClass): NameMapEntry
        {
            $newEntry = new NameMapEntry();

            if (isset($stdClass->id)) {
                $newEntry->id = $stdClass->id;
            }

            if (isset($stdClass->type)) {
                $newEntry->type = $stdClass->type;
            }

            if (isset($stdClass->inName)) {
                $newEntry->inName = $stdClass->inName;
            }

            if (isset($stdClass->outName)) {
                $newEntry->outName = $stdClass->outName;
            }

            return $newEntry;
        };
    }

    public function isValid(): bool
    {
        return isset($this->type) && isset($this->inName) && isset($this->outName);
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function clearId(): void
    {
        unset($this->id);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getInName(): string
    {
        return $this->inName;
    }

    public function getOutName(): string
    {
        return $this->outName;
    }

    public function update(NameMapEntry $newEntry): void
    {
        $this->type = $newEntry->type;
        $this->inName = $newEntry->inName;
        $this->outName = $newEntry->outName;
    }

    /**
     *
     * {@inheritDoc}
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): mixed
    {
        return $this->__serialize();
    }
}
