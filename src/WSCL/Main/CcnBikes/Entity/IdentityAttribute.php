<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use WSCL\Main\CcnBikes\Enums\IdentityAttributeType;

class IdentityAttribute
{
    public int $id;
    public string $name;
    public IdentityAttributeGroup $group;
    /** @var IdentityAttributeOption[] */
    public array $options;
    public ?string $defaultOption;
    public IdentityAttributeType $valueType;
    public int $sortOrder; // e.g. 1
    public string $userReadOnlyPermissionDescription;
    public bool $readPermission;
    public bool $writePermission;

    public function getOption(string $label): ?IdentityAttributeOption
    {
        $result = array_filter($this->options, fn($attr) => $attr->label == $label);

        return $result[array_key_first($result)] ?? null;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
