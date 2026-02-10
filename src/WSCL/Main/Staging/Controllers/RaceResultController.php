<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Controllers;

use Psr\Log\LoggerInterface;
use RCS\WP\PluginInfoInterface;
use WSCL\Main\EventSeason;
use WSCL\Main\WsclMainOptionsInterface;
use WSCL\Main\RaceResult\RaceResultClient;
use WSCL\Main\RaceResult\Entity\Event;
use WSCL\Main\RaceResult\Entity\EventSettings;

class RaceResultController extends StagingRestController
{
    private const RACE_RESULT_ROUTE = '/raceresult';

    private const EVENTS_ROUTE = self::RACE_RESULT_ROUTE.'/events';
    private const EVENTS_BY_YEAR_ROUTE = self::EVENTS_ROUTE.'/year/(?P<year>[\d]+)';
    private const EVENTS_BY_SEASON_ROUTE = self::EVENTS_ROUTE.'/season/(?P<season>[A-Z]+)';
    private const EVENTS_BY_EVENT_ROUTE = self::EVENTS_ROUTE.'/event/(?P<eventId>[\d]+)';
    private const EVENT_SETTINGS = self::RACE_RESULT_ROUTE.'/event/(?P<eventId>[\d]+)';
    private const SEASON_POINTS_ROUTE = self::RACE_RESULT_ROUTE.'/seasonPoints/(?P<eventId>[\d]+)';
    private const RIDER_TIMING_DATA_ROUTE = self::RACE_RESULT_ROUTE.'/riderTimingData/(?P<eventId>[\d]+)';

    public function __construct(
        PluginInfoInterface $pluginInfo,
        int $apiVersion,
        string $apiRoute,
        WsclMainOptionsInterface $options,
        private RaceResultClient $rrClient,
        LoggerInterface $logger
        )
    {
        parent::__construct($pluginInfo, $apiVersion, $apiRoute, $options, $logger);
    }

    /**
     * {@inheritDoc}
     * @see \RCS\WP\Rest\RestController::initializeInstance()
     */
    protected function initializeInstance(): void
    {
        parent::initializeInstance();

        $this->addEndpoint(self::EVENTS_ROUTE, $this, 'getEvents', 'GET');
        $this->addEndpoint(self::EVENTS_BY_YEAR_ROUTE, $this, 'getEventsByYear', 'GET');
        $this->addEndpoint(self::EVENTS_BY_SEASON_ROUTE, $this, 'getEventsBySeason', 'GET');
        $this->addEndpoint(self::EVENTS_BY_EVENT_ROUTE, $this, 'getEventsByEvent', 'GET');
        $this->addEndpoint(self::EVENT_SETTINGS, $this, 'getEventSettings', 'GET');
        $this->addEndpoint(self::SEASON_POINTS_ROUTE, $this, 'getSeasonPoints', 'GET');
        $this->addEndpoint(self::RIDER_TIMING_DATA_ROUTE, $this, 'getRiderTimingData', 'GET');
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Controllers\StagingRestController::getItemRoute()
     */
    public function getItemRoute(): string
    {
        return self::RACE_RESULT_ROUTE;
    }

    /**
     * REST endpoint handler to return list of RaceResult events.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getEvents(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;

        $events = $this->rrClient->getEvents();

        if (null !== $events) {
            foreach ($events as $event) {
                $settings = $this->rrClient->getEventSettings($event->getId());
                $event->attributes = $settings->attributes;
                $event->attributes->eventSeason = $event->getEventSeason();
            }

            $result = $events;
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to return list of RaceResult events.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getEventsByYear(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;

        $year = intval($request['year']);

        /** @var NULL|Event[] */
        $events = $this->rrClient->getEvents($year);

        if (null !== $events) {
            foreach ($events as $event) {
                $settings = $this->rrClient->getEventSettings($event->getId());
                $event->attributes = $settings->attributes;
            }

            $result = $events;
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to return list of RaceResult events for a particular season.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getEventsBySeason(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;

        $season = EventSeason::tryFrom($request['season']);
        if (is_null($season)) {
            $result = new \WP_Error(self::PARAMETER_ERROR, 'Invalid season specified', array('status' => 400));
        } else {
            /** @var \WSCL\Main\RaceResult\Entity\Event[] */
            $events = $this->rrClient->getEvents();

            $events = array_filter($events, fn($entry) => $entry->getEventSeason() === $season);

            if (!empty($events)) {
                foreach ($events as $event) {
                    $settings = $this->rrClient->getEventSettings($event->getId());
                    $event->attributes = $settings->attributes;
                }

                $result = array_values($events);
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
            }
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to return list of RaceResult events relative to a specific event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getEventsByEvent(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;

        $eventId = intval($request['eventId']);

        /** @var NULL|EventSettings */
        $eventSettings = $this->rrClient->getEventSettings($eventId);

        if (!is_null($eventSettings)) {
            /** @var NULL|Event[] */
            $rrEvents = $this->rrClient->getEvents();

            if (null !== $rrEvents) {
                foreach ($rrEvents as $rrEvent) {
                    $settings = $this->rrClient->getEventSettings($rrEvent->getId());
                    $rrEvent->attributes = $settings->attributes;
                    $rrEvent->attributes->eventSeason = $rrEvent->getEventSeason($eventSettings->getDate());
                }

                $result = $rrEvents;
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }
    /**
     * REST endpoint handler to return the settings for a RaceResult event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getEventSettings(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;

        $eventId = intval($request['eventId']);

        $eventSettings = $this->rrClient->getEventSettings($eventId);

        if (null !== $eventSettings) {
            $result = $eventSettings;
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to return a season points for a specific event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getSeasonPoints(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;
        $eventId = intval($request['eventId']);

        $result = $this->rrClient->fetchSeasonPoints($eventId);

        if (null == $result) {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to return the timing data for a specific event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getRiderTimingData(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;
        $eventId = intval($request['eventId']);

        $result = $this->rrClient->fetchRiderTimingStats($eventId);

        if (null == $result) {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }
}
