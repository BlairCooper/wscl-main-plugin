<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Csv;

use RCS\Csv\CsvBindByName;
use RCS\Csv\CsvBindByNameTrait;
use WSCL\Main\CcnBikes\Enums\MembershipReportStatus;

/**
 * This class represents a row from the "Memberships - All" report from the
 * CCN system.
 */
class MembershipRcd
{
    use CsvBindByNameTrait;

    #[CsvBindByName(column: ["CCN Membership ID", "CCN Identity ID"])]
    public int $ccnIdentityId;

    #[CsvBindByName(column: 'Status')]
    public MembershipReportStatus $status;

    #[CsvBindByName(column: 'First Name')]
    public String $firstName;

    #[CsvBindByName(column: 'Last Name')]
    public String $lastName;

    #[CsvBindByName(column: 'E-Mail')]
    public String $email;

    #[CsvBindByName(column: 'Member Street 1')]
    public String $address;

    #[CsvBindByName(column: 'Member City')]
    public String $city;

    #[CsvBindByName(column: 'Member Province')]
    public String $state;

    #[CsvBindByName(column: 'Member Postal Code')]
    public String $zipCode;

    #[CsvBindByName(column: 'Member Telephone')]
    public String $phone;

    #[CsvBindByName(column: 'Valid Groups')]
    public String $groups;

    public function getRegSysId(): int
    {
        return $this->ccnIdentityId;
    }

    public function getStatus():  MembershipReportStatus
    {
        return $this->status;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastName = $lastname;
    }

    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function isCoach(): bool
    {
        return preg_match("/^Coach.*/", $this->groups);
    }

    public function isAthlete(): bool
    {
        // Could be "Student Athlete" (2022), "Grade d"(early spring 2023) or just "d"(spring 2023)
        return preg_match("/^(Student.*|Grade \\d|\\d)/", $this->groups);
    }

    public function isStaff(): bool
    {
        return !$this->isCoach() && !$this->isAthlete();
    }

    public function abbreviateState(): void
    {
        if (preg_match("/^[I,i]daho.*/", $this->state)) {
            $this->state = "ID";
        } elseif (preg_match("/^[W,w]ashington.*/", $this->state)) {
            $this->state = "WA";
        }
    }

    public function __toString()
    {
        return sprintf('Member: %s (%s, %s)', $this->email, $this->lastName, $this->firstName);
    }
}
