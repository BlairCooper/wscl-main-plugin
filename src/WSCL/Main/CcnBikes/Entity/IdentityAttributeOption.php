<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class IdentityAttributeOption
{
    public int $id;
    public string $label;
    public IdentityAttributeOptionAttribute $attribute;
    public int $sortOrder;

    public function __toString(): string
    {
        return $this->label;
    }
}
