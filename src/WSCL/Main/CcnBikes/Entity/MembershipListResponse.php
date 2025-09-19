<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class MembershipListResponse extends PagedResponse
{
    /** @var MembershipListEntry[] */
    public array $results;
}
