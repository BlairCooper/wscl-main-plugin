<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class MembershipOrgResp extends PagedResponse
{
    /** @var MembershipOrg[] */
    public array $results;
}
