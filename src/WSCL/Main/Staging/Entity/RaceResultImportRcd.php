<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use Psr\Log\LoggerInterface;
use RCS\Csv\CsvBindByName;
use WSCL\Main\Staging\RiderAttributeUpdate;


/**
 * Represents a row from the StagingAppSeasonPoints report from RaceResult.
 *
 * Used to add the RaceResult ID and SeasonPoints to each rider.
 *
 * Assumption: The RegSysID field in RaceResult has been populated for each
 * rider with the rider identifier exported for the registration system.
 */
class RaceResultImportRcd extends TimingImportRcd
{
    #[CsvBindByName(column: 'ID')]
    public ?int $id;

    #[CsvBindByName(column: 'RegSysID')]
    public ?int $regSysId;

    #[CsvBindByName(column: 'Bib')]
    public ?int $bibNumber;

    #[CsvBindByName(column: 'SeasonPoints')]
    public int $seasonPoints;

    #[CsvBindByName(column: 'Firstname')]
    public string $firstname;

    #[CsvBindByName(column: 'Lastname')]
    public string $lastname;

    #[CsvBindByName(column: 'Division')]
    public string $division;

    #[CsvBindByName(column: 'StagingScore')]
    public float $stagingScore;

    #[CsvBindByName(column: 'RaceCount')]
    public int $raceCnt;

    #[CsvBindByName(column: 'HasFirstPlaceFinish')]
    public bool $hasFirstPlaceFinish;

    protected string $gender;
    protected \DateTime $dateOfBirth;

    protected int $lastSeasonPoints;
    protected int $previousSeasonPoints;

    protected float $lastStagingScore;
    protected float $previousStagingScore;

    public function __construct(int $regSysId = null)
    {
        $this->regSysId = $regSysId;
        $this->seasonPoints = 0;
        $this->lastSeasonPoints = 0;
        $this->previousSeasonPoints = 0;
        $this->division = '';
        $this->stagingScore = 0;
        $this->lastStagingScore = 0;
        $this->previousStagingScore = 0;
        $this->raceCnt = 0;
        $this->hasFirstPlaceFinish = false;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getTimingSysId()
     */
    public function getTimingSysId(): int
    {
        return $this->id ?? 0;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setTimingSysId()
     */
    public function setTimingSysId(int $timingSysId): void
    {
        $this->id = $timingSysId;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getRegSysId()
     */
    public function getRegSysId(): ?int
    {
        return $this->regSysId;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setRegSysId()
     */
    public function setRegSysId(int $regSysId): void
    {
        $this->regSysId = $regSysId;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getBibNumber()
     */
    public function getBibNumber(): int
    {
        return $this->bibNumber ?? 0;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setBibNumber()
     */
    public function setBibNumber(int $bibNumber): void
    {
        $this->bibNumber = $bibNumber;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getSeasonPoints()
     */
    public function getSeasonPoints(): int
    {
        return $this->seasonPoints;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setSeasonPoints()
     */
    public function setSeasonPoints(int $points): void
    {
        $this->seasonPoints = $points;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getLastSeasonPoints()
     */
    public function getLastSeasonPoints(): int
    {
        return $this->lastSeasonPoints;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setLastSeasonPoints()
     */
    public function setLastSeasonPoints(int $points): void
    {
        $this->lastSeasonPoints = $points;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getPreviousSeasonPoints()
     */
    public function getPreviousSeasonPoints(): int
    {
        return $this->previousSeasonPoints;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setPreviousSeasonPoints()
     */
    public function setPreviousSeasonPoints(int $points): void
    {
        $this->previousSeasonPoints = $points;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getFirstName()
     */
    public function getFirstName(): string
    {
        return $this->firstname;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setFirstName()
     */
    public function setFirstName(string $name): void
    {
        $this->firstname = $name;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getLastName()
     */
    public function getLastName(): string
    {
        return $this->lastname;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setLastName()
     */
    public function setLastName(string $name): void
    {
        $this->lastname = $name;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getDivision()
     */
    public function getDivision(): string
    {
        return $this->division;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setDivision()
     */
    public function setDivision(string $division): void
    {
        $this->division = $division;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\IValidateRider::validateRider()
     */
    public function validateRider(LoggerInterface $logger, RiderAttributeUpdate $attrUpdate): bool
    {
        return true;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\IValidateRider::isMissingData()
     */
    public function isMissingData(): bool
    {
        return false;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getStagingScore()
     */
    public function getStagingScore(): float
    {
        return $this->stagingScore;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setStagingScore()
     */
    public function setStagingScore(float $score): void
    {
        $this->stagingScore = $score;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getLastStagingScore()
     */
    public function getLastStagingScore(): float
    {
        return $this->lastStagingScore;
    }

    public function setLastStagingScore(float $score): void
    {
        $this->lastStagingScore = $score;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getPreviousStagingScore()
     */
    public function getPreviousStagingScore(): float
    {
        return $this->previousStagingScore;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setPreviousStagingScore()
     */
    public function setPreviousStagingScore(float $score): void
    {
        $this->previousStagingScore = $score;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getRaceCnt()
     */
    public function getRaceCnt(): int
    {
        return $this->raceCnt;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setRaceCnt()
     */
    public function setRaceCnt(int $cnt): void
    {
        $this->raceCnt = $cnt;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\PersonInfoIntf::getId()
     */
    public function getId(): int
    {
        return $this->regSysId;
    }

    /**
     *
     * @param \DateTime $dateOfBirth
     */
    public function setDateOfBirth(\DateTime $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\PersonInfoIntf::getBirthDate()
     */
    public function getBirthDate(): \DateTime
    {
        return $this->dateOfBirth;
    }

    /**
     *
     * @param string $gender
     */
    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\PersonInfoIntf::getGender()
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::setHasFirstPlaceFinish()
     */
    public function setHasFirstPlaceFinish($hasFirstPlaceFinish): void
    {
        $this->hasFirstPlaceFinish = $hasFirstPlaceFinish;
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Entity\TimingRcd::getHasFirstPlaceFinish()
     */
    public function getHasFirstPlaceFinish(): bool
    {
        return $this->hasFirstPlaceFinish;
    }
}
