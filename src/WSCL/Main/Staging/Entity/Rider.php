<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use WSCL\Main\Staging\Types\TeamSizeMap;

/**
 * This class represents a rider within the staging app.
 */
class Rider     // NOSONAR - ignore too many methods
{
    private RegistrationRcd $regRcd;
    private TimingRcd $timingRcd;

    private int $stagingRow;
    private int $startOrder;
    private int $randomNumber;
    private string $division;
    private string $waveId;


    public function __construct(RegistrationRcd $regRcd, TimingRcd $timingRcd, TeamSizeMap $sizeMap)
    {
        $this->regRcd = $regRcd;
        $this->timingRcd = $timingRcd;

        $this->waveId = '';
        $this->startOrder = 0;
        $this->randomNumber = mt_rand();
        $this->division = $this->selectDivision(
            $regRcd->getCategory(),
            $timingRcd->getDivision(),
            $sizeMap->getDivisionSuffix($regRcd->getTeam(), self::isHighSchool($regRcd->getCategory())));
    }

    public static function isHighSchool(string $category): bool
    {
        return boolval(preg_match("/^(High School|Beginner|Intermediate|JV|Varsity|Open).*$/", $category));
    }

    /**
     * Select the division based on the category.
     *
     * When the division is already set in the timing system it is not
     * altered.
     *
     * @param string $category  The rider's category
     * @param string $timingDivision The division set in the timing system
     * @param string $divisionSuffix The division suffix, i.e. D1 vs D2
     *
     * @return string The selected division, or the existing division from
     *          the timing system if present.
     */
    private function selectDivision(string $category, string $timingDivision, string $divisionSuffix): string
    {
        if (!empty($timingDivision)) {
            $result = $timingDivision;
        } else {
            $result = 'Middle School';

            if (self::isHighSchool($category)) {
                $result = 'High School';
            }

            if (0 != strlen($divisionSuffix)) {
                $result = $result . ' ' . $divisionSuffix;
            }
        }

        return $result;
    }

    public function getRegSysId(): int
    {
        return $this->regRcd->getRegSysId();
    }

    public function getTimingSysId(): int
    {
        return $this->timingRcd->getTimingSysId();
    }

    public function getBibNumber(): int
    {
        return $this->timingRcd->getBibNumber();
    }

    public function getGrade(): int
    {
        return $this->regRcd->getGrade();
    }

    public function getStagingRow(): int
    {
        return $this->stagingRow;
    }

    public function setStagingRow(int $row): void
    {
        $this->stagingRow = $row;
    }

    public function getCurrentSeasonPoints(): int
    {
        return $this->timingRcd->getSeasonPoints();
    }

    public function getLastSeasonPoints(): int
    {
        return $this->timingRcd->getLastSeasonPoints();
    }

    public function getPreviousSeasonPoints(): int
    {
        return $this->timingRcd->getPreviousSeasonPoints();
    }

    /**
     * Fetch a staging score for the rider.
     *
     * If there is a score for the current season it is returned. If not, the
     * score from the last season (less 10%) is returned. If there is no
     * current or last season score, the score from the previous (before the
     * last) season is returned (less 20%). If no scores are available, 0 is
     * returned.
     *
     * @return float A staging score for the rider.
     */
    public function getStagingScore(): float
    {
        $score = $this->timingRcd->getStagingScore();

        if (0 == $score) {
            $score = $this->timingRcd->getLastStagingScore() * 0.9;

            if (0 == $score) {
                $score = $this->timingRcd->getPreviousStagingScore() * 0.8;
            }
        }

        return $score;
    }

    public function getCurrentStagingScore(): float
    {
        return $this->timingRcd->getStagingScore();
    }

    public function getLastStagingScore(): float
    {
        return $this->timingRcd->getLastStagingScore();
    }

    public function getPreviousStagingScore(): float
    {
        return $this->timingRcd->getPreviousStagingScore();
    }

    public function getRaceCount(): int
    {
        return $this->timingRcd->getRaceCnt();
    }

    public function hasFirstPlaceFinish(): bool
    {
        return $this->timingRcd->getHasFirstPlaceFinish();
    }

    public function getRandomNumber(): int
    {
        return $this->randomNumber;
    }

    public function getFirstName(): string
    {
        return $this->regRcd->getFirstName();
    }

    public function getNickname(): string
    {
        return $this->regRcd->getNickname();
    }

    public function getLastName(): string
    {
        return $this->regRcd->getLastName();
    }

    public function getGender(): string
    {
        return $this->regRcd->getRaceGender();
    }

    public function getLicense(): string
    {
        return $this->regRcd->getLicense();
    }

    public function getTeam(): string
    {
        return $this->regRcd->getTeam();
    }

    public function getDivision(): string
    {
        return $this->division;
    }

    public function getCategory(): string
    {
        return $this->regRcd->getCategory();
    }

    public function getWaveId(): string
    {
        return $this->waveId;
    }

    public function setWaveId(string $wave): void
    {
        $this->waveId = $wave;
    }

    public function getWave(): string
    {
        $wave = $this->getCategory();

        if (strlen($this->waveId) > 0) {
            $wave .= ' Wave ' . $this->waveId;
        }

        return $wave;
    }

    public function setStartOrder(int $startOrder): void
    {
        $this->startOrder = $startOrder;
    }

    public function getStartOrder(): int
    {
        return $this->startOrder;
    }

    public function getRegistrationRcd(): RegistrationRcd
    {
        return $this->regRcd;
    }

    public function getBirthDate(): \DateTime
    {
        return $this->regRcd->getBirthDate();
    }

    public function __toString()
    {
        return
        sprintf(
            "%4d %-20s %-20s %2d %1s %-50s %-30s %1s %2d %d",
            $this->getBibNumber(),
            $this->getFirstName(),
            $this->getLastName(),
            $this->getGrade(),
            $this->getGender(),
            $this->getTeam(),
            $this->getCategory(),
            $this->waveId,
            $this->stagingRow,
            $this->getRegSysId()
            );
    }
}
