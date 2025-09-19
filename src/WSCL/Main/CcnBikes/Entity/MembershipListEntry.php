<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use WSCL\Main\CcnBikes\Enums\MembershipStatusEnum;

class MembershipListEntry
{
    public int $id;

    public MembershipStatusEnum $status;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): MembershipStatusEnum
    {
        return $this->status;
    }
/*
        "status_display": "Issued",
        "identity_snapshot": {
            "id": 1519288,
            "first_name": "Vincent",
            "last_name": "Bang",
            "email": "marlitabang@yahoo.com",
            "date_of_birth": "2010-06-23",
            "original": 100438079,
            "full_name": "Vincent Bang"
        },
        "membership_organization": {
            "id": 409,
            "name": "Washington Student Cycling League Student Athletes Fall 2022",
            "slug": "washington-student-cycling-league",
            "contact_info": null,
            "address": null
        },
        "complete_purchased_groups": [
            {
                "id": 544331,
                "name": "Student Athlete"
            }
        ],
        "generated_numbers": [],
        "documents": [],
        "checked_out_at": "2022-09-14T16:03:45.124300"
*/
}
