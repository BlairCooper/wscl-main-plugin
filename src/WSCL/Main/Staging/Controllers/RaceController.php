<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Controllers;

use JsonMapper\JsonMapperInterface;
use JsonMapper\Middleware\FinalCallback;
use JsonMapper\ValueObjects\PropertyMap;
use JsonMapper\Wrapper\ObjectWrapper;
use Psr\Log\LoggerInterface;
use RCS\WP\PluginInfoInterface;
use WSCL\Main\WsclMainOptionsInterface;
use WSCL\Main\Staging\Models\Race;

class RaceController extends StagingRestController
{
    private const RACE_NOT_FOUND = 'Race Not Found';

    private const RACES_ROUTE = '/events/(?P<eventId>[\d]+)/races';
    private const RACE_ROUTE  = self::RACES_ROUTE.'/(?P<raceId>[\d]+)';

    private int $nextRaceId = 1;

    public function __construct(
        PluginInfoInterface $pluginInfo,
        int $apiVersion,
        string $apiRoute,
        WsclMainOptionsInterface $options,
        LoggerInterface $logger
        )
    {
        parent::__construct($pluginInfo, $apiVersion, $apiRoute, $options, $logger);

        $this->mapper->unshift(new FinalCallback(
            function (
                \stdClass $json,
                ObjectWrapper $objWrapper,
                PropertyMap $propMap,
                JsonMapperInterface $mapper)
            {
                if ($objWrapper->getObject() instanceof Race) {
                    /** @var Race */
                    $race = $objWrapper->getObject();
                    if (null === $race->getId() || $race->getId() <= 0) {
                        $race->setId($this->nextRaceId++);
                    }
                }
            },
            false
            )
        );
    }

    /**
     * {@inheritDoc}
     * @see \RCS\WP\Rest\RestController::initializeInstance()
     */
    protected function initializeInstance(): void
    {
        parent::initializeInstance();

        $this->addEndpoint(self::RACES_ROUTE, $this, 'getRaces', 'GET');
        $this->addEndpoint(self::RACES_ROUTE, $this, 'createRace', 'POST');
        $this->addEndpoint(self::RACE_ROUTE, $this, 'getRace', 'GET');
        $this->addEndpoint(self::RACE_ROUTE, $this, 'updateRace', 'PUT');
        $this->addEndpoint(self::RACE_ROUTE, $this, 'deleteRace', 'DELETE');
    }


    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Controllers\StagingRestController::getItemRoute()
     */
    public function getItemRoute(): string
    {
        return self::RACE_ROUTE;
    }

    /**
     * REST endpoint handler to return list of races for an events.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getRaces(\WP_REST_Request $request): \WP_REST_Response|\WP_Error    // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);

        $event = $this->fetchEvent($eventId);

        if (null != $event) {
            $result = $event->races;
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to return a specific race.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getRace(\WP_REST_Request $request): \WP_REST_Response|\WP_Error // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);
        $raceId = intval($request['raceId']);

        list ($post, $event) = $this->fetchPostAndEvent($eventId);
        if (null != $post) {
            $race = $event->getRace($raceId);
            if (null !== $race) {
                $result = $race;
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::RACE_NOT_FOUND, array ('status' => 404));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to create a race an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function createRace(\WP_REST_Request $request): \WP_REST_Response|\WP_Error  // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);

        $jsonPayload = (string)$request->get_body();

        /** @var Race */
        $race = new Race();
        $this->mapper->mapObjectFromString($jsonPayload, $race);

        list($post, $event) = $this->fetchPostAndEvent($eventId);

        if (null != $post) {
            if ($event->addRace($race)) {

                $post->post_content = json_encode($event);
                if (wp_update_post($post) == $eventId) {
                    $result = $race;
                } else {
                    $result = new \WP_Error(self::INTERNAL_ERROR, 'Unable to create race', array ('status' => 400));
                }
            } else {
                $result = new \WP_Error(self::DATA_ERROR, 'race already exists', array('status' => 400));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to update a race for an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function updateRace(\WP_REST_Request $request): \WP_REST_Response|\WP_Error  // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);
        $raceId = intval($request['raceId']);

        $jsonPayload = (string)$request->get_body();

        /** @var Race */
        $race = new Race();
        $this->mapper->mapObjectFromString($jsonPayload, $race);

        list($post, $event) = $this->fetchPostAndEvent($eventId);

        if (null !== $post) {
            if ($event->updateRace($raceId, $race)) {

                $post->post_content = json_encode($event);
                if (wp_update_post($post) == $eventId) {
                    $result = $race;
                } else {
                    $result = new \WP_Error(self::INTERNAL_ERROR, 'Unable to create category', array ('status' => 400));
                }
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::RACE_NOT_FOUND, array ('status' => 404));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to delete a race from an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function deleteRace(\WP_REST_Request $request): \WP_REST_Response|\WP_Error  // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);
        $raceId = intval($request['raceId']);

        list($post, $event) = $this->fetchPostAndEvent($eventId);

        if (null !== $post) {
            $race = $event->deleteRace($raceId);

            if (null !== $race) {
                $post->post_content = json_encode($event);
                if (wp_update_post($post) == $eventId) {
                    $result = $race;
                } else {
                    $result = new \WP_Error(self::INTERNAL_ERROR, 'Unable to create category', array ('status' => 400));
                }
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::RACE_NOT_FOUND, array ('status' => 404));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }
}
