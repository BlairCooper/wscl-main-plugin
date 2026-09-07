<?php
declare(strict_types = 1);
namespace WSCL\Main\Usac\Entity;

use \RCS\Json\JsonEntity;

class SearchResult extends JsonEntity
{
    /** @var array<SearchResultEntry> */
    public array $entries;
}
