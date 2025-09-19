<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class IdentityAttributeTuple
{
    public int $attribute;
    public ?int $option;
    public string $value;

    public function __construct(int $id, string $value, ?int $optionId = null)
    {
        $this->attribute = $id;
        $this->value = $value;
        $this->option = $optionId;
    }
}
