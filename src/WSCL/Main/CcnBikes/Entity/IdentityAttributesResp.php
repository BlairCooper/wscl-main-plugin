<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class IdentityAttributesResp extends PagedResponse
{
    /** @var IdentityAttribute[] */
    public array $results;

    public function getAttribute(string $name): ?IdentityAttribute
    {
        $result = array_filter($this->results, fn($attr) => $attr->name == $name);

        return $result[array_key_first($result)] ?? null;
    }
}
