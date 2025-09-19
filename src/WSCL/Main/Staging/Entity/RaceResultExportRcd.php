<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use RCS\Csv\CsvBindByName;
use RCS\Csv\CsvBindByNameTrait;

/**
 * This class is intended to represent rows to be imported back into the
 * RaceResult timing system.
 */
class RaceResultExportRcd
{
    use CsvBindByNameTrait;

    /*
     * Built-in RaceResult fields
     */
    #[CsvBindByName(column: 'ID')]
    public int $id;

    #[CsvBindByName(column: 'Bib')]
    public int $bibNumber;

    #[CsvBindByName(column: 'Firstname')]
    public string $firstname;

    #[CsvBindByName(column: 'Lastname')]
    public string $lastname;

    #[CsvBindByName(column: 'Gender')]
    public string $gender;

    #[CsvBindByName(column: 'DateOfBirth')]
    public string $birthDate;

    #[CsvBindByName(column: 'Contest')]
    public string $category;

    #[CsvBindByName(column: 'Club')]
    public string $team;

    #[CsvBindByName(column: 'License')]
    public string $usacLicense;

    /*
     * Additional Fields
     */
    #[CsvBindByName(column: 'Division')]
    public string $division;

    #[CsvBindByName(column: 'WaveCategory')]
    public string $waveCategory;

    #[CsvBindByName(column: 'Row')]
    public int $row;

    #[CsvBindByName(column: 'Grade')]
    public int $grade;

    #[CsvBindByName(column: 'Nickname')]
    public string $nickname;

    #[CsvBindByName(column: 'RegSysID')]
    public int $regSysId;

    public function fromRider(Rider $rider): RaceResultExportRcd
    {
        // Built-in fields
        $this->setId($rider->getTimingSysId());
        $this->setBibNumber($rider->getBibNumber());

        $this->firstname = $rider->getFirstName();
        $this->lastname = $rider->getLastName();
        $this->gender = $rider->getGender();
        $this->birthDate = $rider->getBirthDate()->format('Y-m-d');

        $this->category = $rider->getCategory();
        $this->team = $rider->getTeam();
        $this->usacLicense = $rider->getLicense();

        // Additional Fields
        $this->division = $rider->getDivision();
        $this->waveCategory = $rider->getWave();
        $this->row = $rider->getStagingRow();
        $this->grade = $rider->getGrade();
        $this->nickname = $rider->getNickname();
        $this->regSysId = $rider->getRegSysId();

        return $this;
    }

    private function setId(int $id): void
    {
        if (0 != $id) {
            $this->id = $id;
        }
    }

    private function setBibNumber(int $bibNumber):void
    {
        if (0 != $bibNumber) {
            $this->bibNumber = $bibNumber;
        }
    }
}
