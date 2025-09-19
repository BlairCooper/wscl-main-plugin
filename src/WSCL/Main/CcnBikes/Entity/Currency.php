<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class Currency
{
    public int $id;
    public string $symbol;
    public string $currency;
    public string $name;

    public static function initUSD(): Currency
    {
        $obj = new Currency();

        $obj->id = 3;
        $obj->symbol = '$';
        $obj->currency = 'USD';
        $obj->name = 'USD $';

        return $obj;
    }
}
