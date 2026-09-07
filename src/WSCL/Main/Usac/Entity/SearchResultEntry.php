<?php
declare(strict_types = 1);
namespace WSCL\Main\Usac\Entity;

class SearchResultEntry
{
    public string $text;    // e.g. "Bryan Sheffield (#813174)"
    public string $value;   // e.g. "/pub/athletes/813174/results"
}
