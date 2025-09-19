<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use RCS\Csv\CsvBindByName;
use RCS\Csv\CsvBindByNameTrait;

class RacePlateRcd
{
    use CsvBindByNameTrait;

    #[CsvBindByName(column: 'Bib')]
    public int $bib;

    #[CsvBindByName(column: 'PlateName')]
    public string $plateName = '';

    #[CsvBindByName(column: 'FullName')]
    public string $fullName = '';

    #[CsvBindByName(column: 'BirthDate')]
    public string $birthDate = '';

    #[CsvBindByName(column: 'Category')]
    public string $raceCategory = '';

    #[CsvBindByName(column: 'Gender')]
    public string $gender = '';

    #[CsvBindByName(column: 'Team')]
    public string $team = '';

    #[CsvBindByName(column: 'Grade')]
    public int $grade = 0;

    #[CsvBindByName(column: 'Parents')]
    public string $parentNames = '';

    #[CsvBindByName(column: 'Phone')]
    public string $parentPhones = '';

    #[CsvBindByName(column: 'EmContact')]
    public string $emergencyContacts = '';

    #[CsvBindByName(column: 'MedicalInfo')]
    public string $medicalInfo = '';

    #[CsvBindByName(column: 'Allergies')]
    public string $allergyInfo = '';

    #[CsvBindByName(column: 'AsthmaInfo')]
    public string $asthmaInfo = '';

//    @CsvCustomBindByPosition(position = 14, converter = BooleanToYesNo.class)
    #[CsvBindByName(column: 'IbuprofenOk')]
    public string $ibuprofenOk = '';
}
