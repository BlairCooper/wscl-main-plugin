<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Csv;

use RCS\Csv\CsvBindByName;

/**
 * This class represents a row from the "Memberships - All" report from the
 * CCN system for Student Athlete organizations.
 */
class AthleteMembershipRcd extends MembershipRcd
{
    #[CsvBindByName(column: 'Parent/Guardian 1 : Email')]
    public String $parent1Email;

    #[CsvBindByName(column: 'Parent/Guardian 1 : First Name')]
    public String $parent1Firstname;

    #[CsvBindByName(column: 'Parent/Guardian 1 : Last Name')]
    public String $parent1Lastname;

    #[CsvBindByName(column: 'Parent/Guardian 1 : Cell Phone Number (xxx-xxx-xxxx)')]
    public String $parent1CellPhone;

    #[CsvBindByName(column: 'Parent/Guardian 1 : Home Phone Number (xxx-xxx-xxxx)')]
    public String $parent1HomePhone;

    #[CsvBindByName(column: 'Parent/Guardian 1 : Work Phone Number (xxx-xxx-xxxx)')]
    public String $parent1WorkPhone;

    #[CsvBindByName(column: 'Parent/Guardian 2 : Email')]
    public String $parent2Email;

    #[CsvBindByName(column: 'Parent/Guardian 2 : First Name')]
    public String $parent2Firstname;

    #[CsvBindByName(column: 'Parent/Guardian 2 : Last Name')]
    public String $parent2Lastname;

    #[CsvBindByName(column: 'Parent/Guardian 2 : Cell Phone Number (xxx-xxx-xxxx)')]
    public String $parent2CellPhone;

    #[CsvBindByName(column: 'Parent/Guardian 2 : Home Phone Number (xxx-xxx-xxxx)')]
    public String $parent2HomePhone;

    #[CsvBindByName(column: 'Parent/Guardian 2 : Work Phone Number (xxx-xxx-xxxx)')]
    public String $parent2WorkPhone;

    public function getParent1Email(): string
    {
        return $this->parent1Email;
    }

    public function setParent1Email(string $email): void
    {
        $this->parent1Email = $email;
    }

    public function getParent1FirstName(): string
    {
        return $this->parent1Firstname;
    }

    public function getParent1LastName(): string
    {
        return $this->parent1Lastname;
    }

    public function getParent1CellPhone(): string
    {
        return $this->parent1CellPhone;
    }

    public function setParent1CellPhone(string $phone): void
    {
        $this->parent1CellPhone = $phone;
    }

    public function getParent1HomePhone(): string
    {
        return $this->parent1HomePhone;
    }

    public function setParent1HomePhone(string $phone): void
    {
        $this->parent1HomePhone = $phone;
    }

    public function getParent1WorkPhone(): string
    {
        return $this->parent1WorkPhone;
    }

    public function setParent1WorkPhone(string $phone): void
    {
        $this->parent1WorkPhone = $phone;
    }

    public function getParent1Phone(): ?string
    {
        $result = null;

        if (!empty($this->parent1CellPhone)) {
            $result = $this->parent1CellPhone;
        } elseif (!empty($this->parent1HomePhone)) {
            $result = $this->parent1HomePhone;
        } elseif (!empty($this->parent1WorkPhone)) {
            $result = $this->parent1WorkPhone;
        }

        return $result;
    }

    public function getParent2Email(): string
    {
        return $this->parent2Email;
    }

    public function setParent2Email(string $email): void
    {
        $this->parent2Email = $email;
    }

    public function getParent2FirstName(): string
    {
        return $this->parent2Firstname;
    }

    public function getParent2LastName(): string
    {
        return $this->parent2Lastname;
    }

    public function getParent2CellPhone(): string
    {
        return $this->parent2CellPhone;
    }

    public function setParent2CellPhone(string $phone): void
    {
        $this->parent2CellPhone = $phone;
    }

    public function getParent2HomePhone(): string
    {
        return $this->parent2HomePhone;
    }

    public function setParent2HomePhone(string $phone): void
    {
        $this->parent2HomePhone = $phone;
    }

    public function getParent2WorkPhone(): string
    {
        return $this->parent2WorkPhone;
    }

    public function setParent2WorkPhone(string $phone): void
    {
        $this->parent2WorkPhone = $phone;
    }

    public function getParent2Phone(): ?string
    {
        $result = null;

        if (!empty($this->parent2CellPhone)) {
            $result = $this->parent2CellPhone;
        } elseif (!empty($this->parent2HomePhone)) {
            $result = $this->parent2HomePhone;
        } elseif (!empty($this->parent2WorkPhone)) {
            $result = $this->parent2WorkPhone;
        }

        return $result;
    }
}
