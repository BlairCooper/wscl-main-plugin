<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class IdentitySnapshot
{
    public int $id;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $gender;
    public \DateTime $dateOfBirth;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

/*
    "identity_snapshot": {
        "id": 1519288,
        "first_name": "Vincent",
        "last_name": "Bang",
        "email": "marlitabang@yahoo.com",
        "gender": "M",
        "date_of_birth": "2010-06-23",
        "nationality": null,
        "original": {
            "id": 100438079,
            "first_name": "Vincent",
            "last_name": "Bang",
            "email": "marlitabang@yahoo.com",
            "gender": "M",
            "date_of_birth": "2010-06-23",
            "nationality": null,
            "citizenship_status": null,
            "is_citizenship_status_verified": false,
            "editable_fields": {
                "first_name": true,
                "last_name": true,
                "date_of_birth": true,
                "email": true,
                "gender": true,
                "nationality": true
            },
            "user": {
                "id": 100275155,
                "username": "marlitabang@yahoo.com",
                "first_name": "Vincent",
                "last_name": "Bang",
                "email": "marlitabang@yahoo.com"
            },
            "is_profile_identity": true,
            "is_looked_up_by_user": false,
            "photo": {},
            "full_name": "Vincent Bang",
            "identity_type": "HUMAN"
        },
        "user": {
            "id": 100275155,
            "username": "marlitabang@yahoo.com",
            "first_name": "Vincent",
            "last_name": "Bang",
            "email": "marlitabang@yahoo.com"
        },
        "citizenship_status": null,
        "is_citizenship_status_verified": false,
        "age": 12,
        "identity_type": "HUMAN"
    }
*/

}
