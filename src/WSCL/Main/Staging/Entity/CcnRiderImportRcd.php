<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use JsonMapper\Middleware\ValueTransformation;
use JsonMapper\Middleware\Rename\Rename;
use Psr\Log\LoggerInterface;
use RCS\Csv\CsvBindByName;
use WSCL\Main\Staging\RiderAttributeUpdate;
use WSCL\Main\Staging\Models\NameMap;

/**
 * This class represents a row from the "WSCL Timing Export" report from the
 * CCN system.
 */
class CcnRiderImportRcd             // NOSONAR - ignore too many methods
    extends RegistrationImportRcd
{
    const RACE_CATEGORY = 'Race Category ';
    const RACE_CATEGORY_CURRENT  = self::RACE_CATEGORY.'Current';
    const RACE_CATEGORY_LAST     = self::RACE_CATEGORY.'Last Season';
    const RACE_CATEGORY_PREVIOUS = self::RACE_CATEGORY.'Previous Season';
    const RACE_PLATE_NAME_MAX_LEN = 12;

    private const HS1_PREFIX = 'High School 1';
    private const HS2_PREFIX = 'High School 2';
    private const HS1_BEGINNER_PREFIX = self::HS1_PREFIX . ' (Beg)';        // Transition prefix for HS1/Beg
    private const HS2_INTERMEDIATE_PREFIX = self::HS2_PREFIX . ' (Int)';    // Transition prefix for HS2/Int
    private const BEGINNER_PREFIX = 'Beginner';                             // Old Beginner prefix
    private const INTERMEDIATE_PREFIX = 'Intermediate';                     // Old Intermediate prefix


    #[CsvBindByName(column: 'CCN Identity ID')]
    public int $ccnIdentityId;

    #[CsvBindByName(column: 'USAC License #')]
    public string $usacLicense;

    #[CsvBindByName(column: 'Race Plate Number')]
    public int $racePlateNumber;

    #[CsvBindByName(column: 'Race Plate Name')]
    public string $racePlateName;

    #[CsvBindByName(column: 'First Name')]
    public string $firstName;

    #[CsvBindByName(column: 'Last Name')]
    public string $lastName;

    #[CsvBindByName(column: 'DOB')]
    //@CsvDate("yyyy/MM/dd")
    public \DateTime $dateOfBirth;

    #[CsvBindByName(column: self::RACE_CATEGORY_CURRENT)]
    public string $raceCatCurrSeason;

    #[CsvBindByName(column: self::RACE_CATEGORY_LAST)]
    public string $raceCatLastSeason = '';

    #[CsvBindByName(column: self::RACE_CATEGORY_PREVIOUS)]
    public string $raceCatPrevSeason = '';

    #[CsvBindByName(column: 'Sex')]
    public string $gender;

    #[CsvBindByName(column: 'Race Gender')]
    public string $raceGender;

    #[CsvBindByName(column: 'Club / Team')]
    public string $team;

    #[CsvBindByName(column: 'Grade')]
    public int $grade;

    #[CsvBindByName(column: 'Parent 1 First Name')]
    public string $parent1FirstName;

    #[CsvBindByName(column: 'Parent 1 Last Name')]
    public string $parent1LastName;

    #[CsvBindByName(column: 'Parent 1 Email')]
    public string $parent1Email;

    #[CsvBindByName(column: 'Parent 1 Cell Phone')]
    public string $parent1CellPhone;

    #[CsvBindByName(column: 'Parent 1 Home Phone Number')]
    public string $parent1HomePhone;

    #[CsvBindByName(column: 'Parent 2 First Name')]
    public string $parent2FirstName;

    #[CsvBindByName(column: 'Parent 2 Last Name')]
    public string $parent2LastName;

    #[CsvBindByName(column: 'Parent 2 Email')]
    public string $parent2Email;

    #[CsvBindByName(column: 'Parent 2 Cell Phone')]
    public string $parent2CellPhone;

    #[CsvBindByName(column: 'Parent 2 Home Phone Number')]
    public string $parent2HomePhone;

    #[CsvBindByName(column: 'Emergency Contact 1: First Name')]
    public string $emergencyContact1FirstName;

    #[CsvBindByName(column: 'Emergency Contact 1: Last Name')]
    public string $emergencyContact1LastName;

    #[CsvBindByName(column: 'Emergency Contact 1: Cell Phone Number')]
    public string $emergencyContact1CellPhone;

    #[CsvBindByName(column: 'Emergency Contact 1: Work Phone Number')]
    public string $emergencyContact1WorkPhone;

    #[CsvBindByName(column: 'Emergency Contact 2: First Name')]
    public string $emergencyContact2FirstName;

    #[CsvBindByName(column: 'Emergency Contact 2: Last Name')]
    public string $emergencyContact2LastName;

    #[CsvBindByName(column: 'Emergency Contact 2: Cell Phone Number')]
    public string $emergencyContact2CellPhone;

    #[CsvBindByName(column: 'Emergency Contact 2: Work Phone Number')]
    public string $emergencyContact2WorkPhone;

    #[CsvBindByName(column: 'Has medical conditions or allergies (Y/N)')]
    public bool $medicalConditionsYN;

    #[CsvBindByName(column: 'Has and manages the following medical conditions or allergies')]
    public string $medicalConditions;

    #[CsvBindByName(column: 'Takes Prescription Medication  (Y/N)')]
    public bool $medicationsYN;

    #[CsvBindByName(column: 'More Information (Medication)')]
    public string $medications;

    #[CsvBindByName(column: 'Food Allergies (Y/N)')]
    public bool $foodAllergiesYN;

    #[CsvBindByName(column: 'More Information (Food Allergies)')]
    public string $foodAllergies;

    #[CsvBindByName(column: 'Has asthma and will have an inhaler (Y/N)')]
    public bool $asthmaYN;

    #[CsvBindByName(column: 'More Information (Asthma)')]
    public string $asthma;

    #[CsvBindByName(column: 'Ibuprofen Authorized (Y/N)')]
    public bool $ibuprofenYN;

    private bool $missingData = false;

    public function isMissingData(): bool
    {
        return $this->missingData;
    }

    /**
     * Fetch the set of season tags for the current, last and previous seasons.
     *
     * E.g. ['Sp2023', 'Fall2022', 'Sp2022']
     *
     * @return string[] The array of season tags
     */
    public static function getSeasonTags(): array
    {
        $year = intval(date("Y"));
        $month = intval(date("m"));

        return array(
            $month <=6 ? 'Sp'.$year : 'Fall'.$year,         // Current season
            $month <=6 ? 'Fall'.($year-1) : 'Sp'.$year,     // Last season
            $month <=6 ? 'Sp'.($year-1) : 'Fall'.($year-1)  // Previous season
            );
    }

    /**
     * Map season/year specific category field names to generic names.
     *
     * Should be injected into the mapper after the RegistrationImportRcd
     * Rename mapper as they are called in reverse order.
     *
     * @return Rename
     */
    public static function getCategorySeasonRenameMapping(): Rename
    {
        /** @var Rename */
        $renameObj = new Rename();

        list ($currSeason, $lastSeason, $prevSeason) = self::getSeasonTags();

        $renameObj->addMapping(self::class, self::RACE_CATEGORY.$currSeason, self::RACE_CATEGORY_CURRENT);
        $renameObj->addMapping(self::class, self::RACE_CATEGORY.$lastSeason, self::RACE_CATEGORY_LAST);
        $renameObj->addMapping(self::class, self::RACE_CATEGORY.$prevSeason, self::RACE_CATEGORY_PREVIOUS);

        return $renameObj;
    }

    public static function getValueTransformer(): ValueTransformation
    {
        return new ValueTransformation(
            static function ($key, $value) {
                switch ($key) {
                    case 'asthmaYN':
                    case 'foodAllergiesYN':
                    case 'ibuprofenYN':
                    case 'medicalConditionsYN':
                    case 'medicationsYN':
                        $value = $value == 'Yes';
                        break;

                    case 'gender':
                    case 'raceGender':
                        $value = strtoupper($value);
                        break;

                    case 'parent1CellPhone':
                    case 'parent1HomePhone':
                    case 'parent2CellPhone':
                    case 'parent2HomePhone':
                    case 'emergencyContact1CellPhone':
                    case 'emergencyContact1WorkPhone':
                    case 'emergencyContact2CellPhone':
                    case 'emergencyContact2WorkPhone':
                        $value = RegistrationImportRcd::formatPhoneNumber($value);
                        break;

                    case 'parent1Email':
                    case 'parent2Email':
                        $value = strtolower(trim($value));
                        break;

                    default:
                        $value = trim($value);
                        break;
                }

                return $value;
            },
            true
            );
    }

    public function validateRider(LoggerInterface $logger, RiderAttributeUpdate $attrUpdate): bool
    {
        if (empty($this->racePlateName)) {
             $this->racePlateName = $this->firstName;
             $attrUpdate->setRacePlateName($this->racePlateName);
        }

        if (strlen($this->racePlateName) > self::RACE_PLATE_NAME_MAX_LEN) {
            $logger->critical(
                sprintf(
                    'Race Plate Name (%s) for %s %s is too long',
                    $this->racePlateName,
                    $this->firstName,
                    $this->lastName
                    )
                );
            $this->missingData = true;
        } else {
            if (0 == strcasecmp($this->racePlateName, $this->firstName . ' ' . $this->lastName)) {
                $logger->error(
                    sprintf(
                        'Race Plate Name same as full name for %s',
                        $this->racePlateName
                        )
                    );
                $this->missingData = true;
            }
        }

        if (empty($this->raceGender)) {
            if ('U' == $this->gender) {
                $logger->critical(
                    sprintf(
                        'Race Gender not set for "Unspecified" rider %s %s with %s',
                        $this->firstName,
                        $this->lastName,
                        $this->team
                        )
                    );
                $this->missingData = true;
            } else {
                $this->raceGender = $this->gender;
                $attrUpdate->setRaceGender($this->raceGender);
            }
        }

        if (empty($this->raceCatCurrSeason) && !empty($this->raceGender)) {
            $category = $this->getRaceCategory();

            if (!strstr($category, 'Open')) {
                $category .= (0 == strcasecmp('F', $this->raceGender) ? " Girls" : " Boys");
            }

            $this->raceCatCurrSeason = $category;
            $attrUpdate->setRaceCategory($this->raceCatCurrSeason);
        }

        $this->team = NameMap::getInstance()->getMappedName('Team', $this->team);

        return !$this->missingData;
    }

    /**
     * Determine category for rider.
     *
     * This function is only called if the rider doesn't have a category for
     * the current season.
     *
     * @return string The riders category
     */
    private function getRaceCategory(): string
    {
        $category = '';

        /** If someone race Open Non-Competitive last season, ignore it */
        if (strstr($this->raceCatLastSeason, 'Open Non')) {
            $this->raceCatLastSeason = '';
        }

        /** If someone race Open Non-Competitive in the previous season, ignore it */
        if (strstr($this->raceCatPrevSeason, 'Open Non')) {
            $this->raceCatPrevSeason = '';
        }
        /*
         * Special case where someone raced Adv MS in the Spring but in a
         * Grade category in the Fall because we don't have Adv MS in the
         * Fall. In this case, copy their Adv MS category to last season.
         */
        if (strstr($this->raceCatPrevSeason, "Adv") && strstr($this->raceCatLastSeason, "Grade")) {
            $this->raceCatLastSeason = $this->raceCatPrevSeason;
        }

        if (empty($this->raceCatLastSeason) &&
            empty($this->raceCatPrevSeason)) {
            $category = $this->getDefaultCategory($this->grade);
        } else {
            // If middle school
            if ($this->grade <= 8) {
                $category = $this->getMiddleSchoolCategory(
                    $this->raceCatLastSeason,
                    $this->raceCatPrevSeason,
                    $this->grade
                    );
            } else { // Must be 9th grade or higher
                $category = $this->getHighSchoolCategory($this->raceCatLastSeason, $this->raceCatPrevSeason);
            }
        }

        return $category;
    }

    private function getDefaultCategory(int $grade): string
    {
        $category = '';

        switch ($grade) {
            case 3:
            case 4:
                $category = "Open Non-Competitive";
                break;

            case 5:
                $category = "5th Grade";
                break;

            case 6:
                $category = "6th Grade";
                break;

            case 7:
                $category = "7th Grade";
                break;

            case 8:
                $category = "8th Grade";
                break;

            case 9:
            case 10:
                $category = self::HS1_PREFIX;
                break;

            case 11:
            case 12:
                $category = self::HS2_PREFIX;
                break;

            default:
                $category = '';
                break;
        }

        return $category;
    }

    private function getMiddleSchoolCategory(
        string $raceCatLastSeason,
        string $raceCatPrevSeason,
        int $grade
        ): string
    {
        $category = null;
        $seasonToCheck = $raceCatLastSeason;

        // If didn't race last season, use previous season
        if (empty($seasonToCheck)) {
            $seasonToCheck = $raceCatPrevSeason;
        }

        // If were advanced or HS cat, stay in category
        if (strstr($seasonToCheck, "Adv") || !strstr($seasonToCheck, "Grade")) {
            $category = $this->extractCategory($seasonToCheck);
        } else {
            // Else get default
            $category = $this->getDefaultCategory($grade);
        }

        return $category;
    }

    private function getHighSchoolCategory(string $raceCatLastSeason, string $raceCatPrevSeason): string
    {
        $category = null;
        $seasonToCheck = $raceCatLastSeason;

        // If didn't race last season, use previous season
        if (empty($seasonToCheck)) {
            $seasonToCheck = $raceCatPrevSeason;
        }

        // MS Adv moves to Intermediate
        if (strstr($seasonToCheck, "Adv")) {
            $category = self::HS2_PREFIX;
        } else {
            // If were in 8th Grade, put in High School 1 / Beginner
            if (strstr($seasonToCheck, "Grade")) {
                $category = self::HS1_PREFIX;
            } else {
                // Otherwise use same category
                $category = $this->extractCategory($seasonToCheck);
            }
        }

        return $category;
    }

    private function extractCategory(string $prevCategory): string
    {
        $category = '';
        $matches = array();

        if (preg_match("/^(.*) (Boys|Girls)$/", $prevCategory, $matches)) {
            $category = $matches[1];
        } else {
            $category = $prevCategory;
        }

        if (self::BEGINNER_PREFIX == $category || self::HS1_BEGINNER_PREFIX == $category) {
            $category = self::HS1_PREFIX;
        } elseif (self::INTERMEDIATE_PREFIX == $category || self::HS2_INTERMEDIATE_PREFIX == $category) {
            $category = self::HS2_PREFIX;
        }

        return $category;
    }

    public function getCategory(): string
    {
        return $this->raceCatCurrSeason;
    }

    public function getLastCategory(): string
    {
        return $this->raceCatLastSeason;
    }

    public function getPrevCategory(): string
    {
        return $this->raceCatPrevSeason;
    }

    public function getRegSysId(): int
    {
        return $this->ccnIdentityId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getNickname(): string
    {
        return $this->racePlateName;
    }

    public function getRaceGender(): string
    {
        return $this->raceGender;
    }

    public function getGender(): string
    {
        return $this->getRaceGender();
    }

    public function getGrade(): int
    {
        return $this->grade;
    }

    public function getLicense(): string
    {
        return $this->usacLicense;
    }

    public function getTeam(): string
    {
        return $this->team;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstName = $firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastName = $lastname;
    }

    public function setNickname(string $nickname): void
    {
        $this->racePlateName = $nickname;
    }

    public function setCategory(string $category): void
    {
        $this->raceCatCurrSeason = $category;
    }

    public function setRegSysId(int $regSysId): void
    {
        $this->ccnIdentityId = $regSysId;
    }

    public function getBirthDate(): \DateTime
    {
        return $this->dateOfBirth;
    }

    public function getParent1FirstName(): string
    {
        return $this->parent1FirstName ?? '';
    }

    public function getParent1LastName(): string
    {
        return $this->parent1LastName ?? '';
    }

    public function getParent1CellPhone(): string
    {
        return $this->parent1CellPhone ?? '';
    }

    public function getParent1HomePhone(): string
    {
        return $this->parent1HomePhone ?? '';
    }

    public function getParent2FirstName(): string
    {
        return $this->parent2FirstName ?? '';
    }

    public function getParent2LastName(): string
    {
        return $this->parent2LastName ?? '';
    }

    public function getParent2CellPhone(): string
    {
        return $this->parent2CellPhone ?? '';
    }

    public function getParent2HomePhone(): string
    {
        return $this->parent2HomePhone ?? '';
    }

    public function getEmergencyContact1FirstName(): string
    {
        return $this->emergencyContact1FirstName ?? '';
    }

    public function getEmergencyContact1LastName(): string
    {
        return $this->emergencyContact1LastName ?? '';
    }

    public function getEmergencyContact1CellPhone(): string
    {
        return $this->emergencyContact1CellPhone ?? '';
    }

    public function getEmergencyContact1WorkPhone(): string
    {
        return $this->emergencyContact1WorkPhone ?? '';
    }

    public function getEmergencyContact2FirstName(): string
    {
        return $this->emergencyContact2FirstName ?? '';
    }

    public function getEmergencyContact2LastName(): string
    {
        return $this->emergencyContact2LastName ?? '';
    }

    public function getEmergencyContact2CellPhone(): string
    {
        return $this->emergencyContact2CellPhone ?? '';
    }

    public function getEmergencyContact2WorkPhone(): string
    {
        return $this->emergencyContact2WorkPhone ?? '';
    }

    public function getMedicalConditions(): string
    {
        return $this->medicalConditions ?? '';
    }

    public function getMedications(): string
    {
        return $this->medications ?? '';
    }

    public function getFoodAlergies(): string
    {
        return $this->foodAllergies ?? '';
    }

    public function getAsthmaInfo(): string
    {
        return $this->asthma ?? '';
    }

    public function hasAsthma(): bool
    {
        return $this->asthmaYN ?? false;
    }

    public function hasMedicalConditions(): bool
    {
        return $this->medicalConditionsYN ?? false;
    }

    public function hasFoodAlergies(): bool
    {
        return $this->foodAllergiesYN ?? false;
    }

    public function isIbuprofenOk(): bool
    {
        return $this->ibuprofenYN ?? false;
    }
    public function getId(): ?int
    {
        return $this->ccnIdentityId;
    }
}
