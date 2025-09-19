<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

/**
 * Interface for retrieving the information about a person.
 */
interface PersonInfoIntf
{
    public function getId(): ?int;
    /**
     * Fetch the first name.
     *
     * @return string The first name
     */
    public function getFirstName(): string;

    /**
     * Fetch the last name.
     *
     * @return string The last name
     */
    public function getLastName(): string;

    public function getGender(): string;
    public function getBirthDate(): \DateTime;
}
