<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

interface RegistrationRcd extends PersonInfoIntf   // NOSONAR - ignore too many methods
{
    /**
     * Fetch the registration identifier for the rider.
     *
     * @return int The registration identifier.
     */
    public function getRegSysId(): int;

    public function getFirstName(): string;
    public function getLastName(): string;
    public function getNickname(): string;
    public function getBirthDate(): \DateTime;
    public function getRaceGender(): string;
    public function getLicense(): string;

    public function getGrade(): int;
    public function getTeam(): string;

    /**
     * A rider's current category
     * @return string
     */
    public function getCategory(): string;
    /**
     * A rider's category last season
     * @return string
     */
    public function getLastCategory(): string;
    /**
     * A rider's category the season prior to the last season
     * @return string
     */
    public function getPrevCategory(): string;

    public function setFirstname(string $firstname): void;
    public function setLastname(string $lastname): void;
    public function setNickname(string $nickname): void;

    public function setCategory(string $category): void;
    public function setRegSysId(int $regSysId): void;

    public function getParent1FirstName(): string;
    public function getParent1LastName(): string;
    public function getParent1CellPhone(): string;
    public function getParent1HomePhone(): string;

    public function getParent2FirstName(): string;
    public function getParent2LastName(): string;
    public function getParent2CellPhone(): string;
    public function getParent2HomePhone(): string;

    public function getEmergencyContact1FirstName(): string;
    public function getEmergencyContact1LastName(): string;
    public function getEmergencyContact1CellPhone(): string;
    public function getEmergencyContact1WorkPhone(): string;

    public function getEmergencyContact2FirstName(): string;
    public function getEmergencyContact2LastName(): string;
    public function getEmergencyContact2CellPhone(): string;
    public function getEmergencyContact2WorkPhone(): string;

    public function getMedicalConditions(): string;
    public function getMedications(): string;
    public function getFoodAlergies(): string;
    public function getAsthmaInfo(): string;

    public function hasAsthma(): bool;
    public function hasMedicalConditions(): bool;
    public function hasFoodAlergies(): bool;
    public function isIbuprofenOk(): bool;
}
