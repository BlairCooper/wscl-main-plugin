<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use WSCL\Main\RaceResult\Entity\SeasonPoints;

class SeasonPointsWrapper extends SeasonPoints implements PersonInfoIntf
{
    public function __construct(SeasonPoints $seasonPoints)
    {
        $vars = get_object_vars($seasonPoints);

        foreach ($vars as $var => $value) {
            $this->$var = $value;
        }
    }

    public function getId(): ?int
    {
        return $this->regSysId;
    }

    public function getFirstName(): string
    {
        return $this->firstname;
    }

    public function getLastName(): string
    {
        return $this->lastname;
    }

    public function getBirthDate(): \DateTime
    {
        return $this->dateOfBirth;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

}

