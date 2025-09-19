<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use WSCL\Main\CcnBikes\Enums\ListingStatusEnum;

class MembershipOrg
{
    public int $id;
    public string $name;
    public string $organizationName;
    public string $slug;
    public string $allowMemberToEditMembership;
    public string $emailConfirmationType;
    public string $manualMembershipCreationPermission;
    public bool $enableMembershipEffectiveDateOverride;
    public bool $showCitizenshipStatus;
    public bool $isPurchasedGroupForeignIdEditable;
    public bool $sendEmailOnMembershipIssued;
    public ListingStatusEnum $listingStatus;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isAthleteOrg(): bool
    {
        return 1 == preg_match('/.*Student Athletes.*/', $this->name);
    }

    public function isCoachOrg(): bool
    {
        return 1 == preg_match('/.*Coach Level 1.*/', $this->name);
    }

    public function isActive(): bool
    {
        return $this->listingStatus == ListingStatusEnum::APPROVED;
    }
}
