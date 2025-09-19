<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class CouponsResponse extends PagedResponse
{
    /** @var Coupon[] */
    public array $results;

    public function getCoupon(string $code): ?Coupon
    {
        $result = array_filter($this->results, fn($attr) => $attr->code == $code);

        return $result[array_key_first($result)] ?? null;
    }
}
