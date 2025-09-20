<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging;

use Psr\Log\LoggerInterface;
use WSCL\Main\RaceResult\RaceResultClient;
use WSCL\Main\Staging\Entity\RaceResultImportRcd;
use WSCL\Main\Staging\Entity\SeasonPointsWrapper;
use WSCL\Main\Staging\Entity\TimingRcd;
use WSCL\Main\Staging\Models\Event;
use WSCL\Main\Staging\Types\RegisteredRiderMap;
use WSCL\Main\Staging\Types\TimingRiderMap;

class TimingLoader
{
    private TimingRiderMap $timingRiderMap;
    private RaceResultClient $rrClient;

    private LoggerInterface $logger;

    public function __construct(
        RaceResultClient $rrClient,
        LoggerInterface $logger
        )
    {
        $this->logger = $logger;

        $this->timingRiderMap = new TimingRiderMap();
        $this->rrClient = $rrClient;
    }

    public function getRiderMap(): TimingRiderMap
    {
        return $this->timingRiderMap;
    }

    public function loadTimingFiles(
        RegisteredRiderMap $regRiderMap,
        Event $event
        ): void
    {
        // Initialize the TimingRiderMap with entries for all the registered riders
        $this->initializeTimingFromRegistrations($regRiderMap);

        // Load current season
        $this->loadRaceResultPoints(
            $event->rrEventId,
            $this->getCurrentSeasonRaceResultCallback()
            );
        // Load last season
        $this->loadRaceResultPoints(
            $event->rrLastEventId,
            $this->getLastSeasonRaceResultCallback()
            );
        // Load the season before that
        $this->loadRaceResultPoints(
            $event->rrPrevEventId,
            $this->getPreviousSeasonRaceResultCallback()
            );
    }

    private function initializeTimingFromRegistrations(RegisteredRiderMap $regRiderMap): void
    {
        foreach ($regRiderMap->values() as $regRcd) {
            $timingRcd = new RaceResultImportRcd($regRcd->getRegSysId());
            $timingRcd->setFirstName($regRcd->getFirstName());
            $timingRcd->setLastName($regRcd->getLastName());
            $timingRcd->setGender($regRcd->getRaceGender());
            $timingRcd->setDateOfBirth($regRcd->getBirthDate());

            $this->timingRiderMap->put($timingRcd);
        }
    }

    private function loadRaceResultPoints(int $eventId, callable $callback): void
    {
        $rrRiders = $this->rrClient->fetchSeasonPoints($eventId);

        if (isset($rrRiders)) {

            foreach ($rrRiders as $riderPoints) {
                $callback(new SeasonPointsWrapper($riderPoints));
            }
        }
    }

    private function checkForNewRegRcd(TimingRcd $timingRcd, SeasonPointsWrapper $seasonPoints): void
    {
        if ($timingRcd->getRegSysId() !== $seasonPoints->getId()) {
            $this->logger->info(
                'New registration record for {fname} {lname}',
                [
                    'fname' => $timingRcd->getFirstName(),
                    'lname' => $timingRcd->getLastName()
                ]
                );
        }
    }

    /**
     * Populate the timing record based on the current season
     *
     * @return callable
     */
    private function getCurrentSeasonRaceResultCallback(): callable
    {
        return function (SeasonPointsWrapper $riderPoints): void
            {
                $timingRcd = $this->timingRiderMap->get($riderPoints);

                if (isset($timingRcd)) {
                    $timingRcd->setTimingSysId($riderPoints->id);
                    $timingRcd->setBibNumber($riderPoints->bib);
                    $timingRcd->setSeasonPoints($riderPoints->seasonPoints);
                    $timingRcd->setStagingScore($riderPoints->stagingScore);
                    $timingRcd->setRaceCnt($riderPoints->raceCnt);
                    $timingRcd->setDivision($riderPoints->division);
                    $timingRcd->setHasFirstPlaceFinish($riderPoints->hasFirstPlaceFinish);
                }
            };
    }

    /**
     * Update the timing record with the points from last season.
     *
     * This would be the season before the current season.
     *
     * @return callable
     */
    private function getLastSeasonRaceResultCallback(): callable
    {
        return function (SeasonPointsWrapper $riderPoints): void
        {
            $timingRcd = $this->timingRiderMap->get($riderPoints);

            if (isset($timingRcd)) {
                $this->checkForNewRegRcd($timingRcd, $riderPoints);
                $timingRcd->setLastSeasonPoints($riderPoints->seasonPoints);
                $timingRcd->setLastStagingScore($riderPoints->stagingScore);
            }
        };
    }

    /**
     * Update the timing record with the points from the previous season.
     *
     * This would be the season prior to the last season, so two seasons ago.
     *
     * @return callable
     */
    private function getPreviousSeasonRaceResultCallback(): callable
    {
        return function (SeasonPointsWrapper $riderPoints): void
            {
                $timingRcd = $this->timingRiderMap->get($riderPoints);

                if (isset($timingRcd)) {
                    $this->checkForNewRegRcd($timingRcd, $riderPoints);
                    $timingRcd->setPreviousSeasonPoints($riderPoints->seasonPoints);
                    $timingRcd->setPreviousStagingScore($riderPoints->stagingScore);
                }
            };
    }
}
