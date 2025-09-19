<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Models;

use WSCL\Main\Staging\Entity\Rider;

class Category
{
    public int $id;
    public string $name;
    public ?int $waves;
    public ?bool $genderNeutral;
    public ?string $plateAbbreviation;

    /** @var array<Rider[]> */
    private array $riderLists = array();

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWaves(): int
    {
        return $this->waves ?? 1;
    }

    public function isGenderNeutral(): bool
    {
        return $this->genderNeutral ?? false;
    }

    public function getPlateAbbreviation(): ?string
    {
        return $this->plateAbbreviation;
    }

    public function update(Category $newCat): void
    {
        $this->name = $newCat->name;
        $this->waves = $newCat->waves;
        $this->genderNeutral = $newCat->genderNeutral;
        $this->plateAbbreviation = $newCat->plateAbbreviation;
    }

    /**
     *
     * @param Rider[] $riderSet
     * @param int $rowSize
     * @param int $attendanceFactor
     */
    public function initializeWaves(array $riderSet, int $rowSize, int $attendanceFactor): void
    {
        // Attendance factor is stored as a whole number, convert to percent
        $attendanceFactor = $attendanceFactor / 100;

        /** @var Rider[] */
        $riderList = array();

        $riderCnt = count($riderSet);

        $this->initializeStartingOrder($riderSet);

        if (1 == $this->waves) {
            $this->riderLists[] = $riderSet;
        } else {
            $waveSize = $this->determineWaveSize($riderCnt, $rowSize, $attendanceFactor);

            $ridersInWaveCnt = 0;
            $ridersLeft = $riderCnt;
            $waveCnt = 1;
            $waveID = 'A';

            foreach ($riderSet as $rider) {
                $rider->setWaveId($waveID);
                $riderList[] = $rider;
                $ridersInWaveCnt++;
                $ridersLeft--;

                if ($ridersInWaveCnt >= $waveSize && $waveCnt != $this->waves) {
                    $ridersInWaveCnt = 0;
                    $waveCnt++;
                    $waveID++;
                    $this->riderLists[] = $riderList;

                    $riderList = array();

                    if ($ridersLeft < $waveSize) {
                        $waveSize = $ridersLeft;
                    }
                }
            }

            if (!empty($riderList)) {
                $this->riderLists[] = $riderList;
            }
        }
    }

    private function determineWaveSize(int $riderCnt, int $rowSize, float $attendanceFactor): int
    {
        $waveSize = (int)($riderCnt * $attendanceFactor / $this->waves);

        // Check if the last row would be more than half full, if so round up the waveSize
        if ((($waveSize % $rowSize) * 2) > $rowSize) {
            // round up to the next full row size
            $waveSize = $waveSize + ($rowSize - ($waveSize % $rowSize));
        } else {
            // round down to the previous full row size
            $waveSize = $waveSize - ($waveSize % $rowSize);
        }

        return $waveSize;
    }

    /**
     *
     * @return array<Rider[]>
     */
    public function getWaveLists(): array
    {
        return $this->riderLists;
    }

    /**
     *
     * @param Rider[] $riderSet
     */
    private function initializeStartingOrder(array $riderSet): void
    {
        $startOrder = 1;

        foreach ($riderSet as $rider) {
            $rider->setStartOrder($startOrder++);
        }
    }
}
