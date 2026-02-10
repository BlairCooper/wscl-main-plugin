<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Controllers;

use WSCL\Main\Staging\StagingHelper;
use WSCL\Main\Staging\Models\Event;

class EventController extends StagingRestController
{
    private const PLURAL_ROUTE = '/events';
    private const SINGULAR_ROUTE = self::PLURAL_ROUTE.'/(?P<eventId>[\d]+)';

    /**
     * {@inheritDoc}
     * @see \RCS\WP\Rest\RestController::initializeInstance()
     */
    protected function initializeInstance(): void
    {
        parent::initializeInstance();

        $this->addEndpoint(self::PLURAL_ROUTE, $this, 'getEvents', 'GET');
        $this->addEndpoint(self::PLURAL_ROUTE, $this, 'createEvent', 'POST');
        $this->addEndpoint(self::SINGULAR_ROUTE, $this, 'getEvent', 'GET');
        $this->addEndpoint(self::SINGULAR_ROUTE, $this, 'updateEvent', 'PUT');
        $this->addEndpoint(self::SINGULAR_ROUTE, $this, 'deleteEvent', 'DELETE');
    }

    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Controllers\StagingRestController::getItemRoute()
     */
    public function getItemRoute(): string
    {
        return self::SINGULAR_ROUTE;
    }

    /**
     * REST endpoint handler to return list of Events.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getEvents(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $postQuery = array(
            'numberposts' => -1,
            'posts_per_page' => -1,
            'post_type' => StagingHelper::CUSTOM_POST_TYPE,
            'orderby' => 'date',
            'order' => 'ASC'
        );

        // Get all of the custom posts
        /** @var \WP_Post[] */
        $posts = get_posts($postQuery);

        $eventArray = array_map(
            fn (\WP_Post $post) => $this->mapper->mapObjectFromString($post->post_content, new Event()),
            $posts
            );

        return rest_ensure_response($eventArray);
    }

    /**
     * REST endpoint handler to return an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getEvent(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $eventId = intval($request['eventId']);

        $event = $this->fetchEvent($eventId);

        if (null !== $event) {
            $result = $event;
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to create an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function createEvent(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;

        $jsonPayload = (string)$request->get_body();

        /** @var Event */
        $event = new Event();
        $this->mapper->mapObjectFromString($jsonPayload, $event);

        $query = new \WP_Query([
            'post_type' => StagingHelper::CUSTOM_POST_TYPE,
            'title' => $event->getName(),
            'posts_per_page' => -1,
            'no_found_rows' => true
        ]);

        if ((null !== $event->getId() && -1 !== $event->getId()) ||
            $query->have_posts()
            ) {
                $result = new \WP_Error(
                    self::DATA_ERROR,
                    'Cannot create an event that already exists',
                    array ('status' => 400)
                    );
            } else {
                $postArray = array();

                $postArray['post_status']   = 'publish';
                $postArray['post_type']     = StagingHelper::CUSTOM_POST_TYPE;
                $postArray['post_name']     = $event->getName();
                $postArray['post_title']    = $event->getName();
                $postArray['post_content']  = 'temporary content';

                // Insert the post into the database
                $postId = wp_insert_post($postArray);

                $event->setId($postId);

                // Ensure that any races times have the same date as the event
                foreach ($event->getRaces() as $race) {
                    $race->syncWithEventDate($event->date);
                }

                $postArray['ID'] = $postId;
                $postArray['post_content']  = json_encode($event);

                if ($postId != wp_update_post($postArray)) {
                    wp_delete_post($postId);

                    $result = new \WP_Error(
                        self::INTERNAL_ERROR,
                        'Unable to save event(update)',
                        array ('status' => 400)
                        );
                } else {
                    $result = $event;
                }
            }

            return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to update an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function updateEvent(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;

        $eventId = intval($request['eventId']);
        $jsonPayload = (string)$request->get_body();

        /** @var Event */
        $event = new Event();
        $this->mapper->mapObjectFromString($jsonPayload, $event);

        // Ensure that the races times have the same date as the event
        foreach ($event->getRaces() as $race) {
            $race->syncWithEventDate($event->date);
        }

        if (null === $event->getId() || $eventId != $event->getId()) {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array ('status' => 404));
        } else {
            $post = $this->fetchEventPost($eventId);

            if (null != $post) {
                $post->post_content = json_encode($event);
                $post->post_title = $event->getName();

                if ($eventId === wp_update_post($post->to_array())) {
                    $result = $event;
                } else {
                    $result = new \WP_Error(self::INTERNAL_ERROR, 'Unable to update event', array ('status' => 400));
                }
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
            }
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to delete an event
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function deleteEvent(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;
        $eventId = intval($request['eventId']);

        list($post, $event) = $this->fetchPostAndEvent($eventId);

        if (null !== $post) {
            $deletedPost = wp_delete_post($post->ID);

            if (null !== $deletedPost && false !== $deletedPost) {
                $result = $event;
            }else {
                $result = new \WP_Error(self::INTERNAL_ERROR, 'Unable to delete event', array ('status' => 400));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }
}
