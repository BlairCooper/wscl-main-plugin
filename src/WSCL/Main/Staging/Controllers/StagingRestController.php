<?php declare(strict_types = 1);
namespace WSCL\Main\Staging\Controllers;

use JsonMapper\JsonMapperInterface;
use Psr\Log\LoggerInterface;
use RCS\WP\PluginInfoInterface;
use RCS\WP\Rest\RestController;
use WSCL\Main\Staging\StagingHelper;
use WSCL\Main\Staging\Models\Event;
use WSCL\Main\Staging\Types\WsclFactoryRegistry;
use WSCL\Main\WsclMainOptionsInterface;

abstract class StagingRestController extends RestController
{
    protected const ACCESS_DENIED = 'access_denied';
    protected const DATA_ERROR = 'data_error';
    protected const INTERNAL_ERROR = 'internal_error';
    protected const NOT_FOUND = 'not_found';
    protected const PARAMETER_ERROR = 'parameter_error';

    protected const EVENT_NOT_FOUND = 'Event Not Found';

    /** @var JsonMapperInterface The JsonMapper to use in mapping JSON to objects */
    protected JsonMapperInterface $mapper;

    public function __construct(
        PluginInfoInterface $pluginInfo,
        int $apiVersion,
        string $apiRoute,
        protected WsclMainOptionsInterface $options,
        LoggerInterface $logger
        )
    {
        parent::__construct($pluginInfo, $apiVersion, $apiRoute, WsclFactoryRegistry::withPhpClassesAdded(true), $logger);
    }

    /**
     * Fetch the route for working with a specific item.
     *
     * The route should generally start with a slash.
     *
     * @return string The route for working with an item.
     */
    abstract public function getItemRoute(): string;

    /**
     * Check if the user has permission to use the API
     *
     * @return \WP_Error|true
     */
    public function permissionCheck(): true|\WP_Error
    {
        $nonceOk = (isset($_SERVER['HTTP_X_WP_NONCE']) && wp_verify_nonce($_SERVER['HTTP_X_WP_NONCE'])) || defined('DEV_ENV');

        if (!$nonceOk &&
            !current_user_can(StagingHelper::CAP_EXECUTE_API) &&
            !is_admin()
            ) { // && !defined('DEV_ENV')) {
            return new \WP_Error(
                self::ACCESS_DENIED,
                'You do not have permission to access this API.',
                array('status' => 401)
                );
        }

        return true;
    }

    /**
     * Fetch an Event object from the database.
     *
     * @param int $eventId The event identifier
     *
     * @return Event|NULL The event object or null if it cannot be found.
     */
    protected function fetchEvent(int $eventId): ?Event
    {
        return $this->fetchPostAndEvent($eventId)[1];
    }

    /**
     * Fetch an Event post from the database.
     *
     * @param int $eventId The event identifier
     *
     * @return \WP_Post|NULL The event object or null if it cannot be found.
     */
    protected function fetchEventPost(int $eventId): ?\WP_Post
    {
        $result = null;

        // Get the custom post
        /** @var \WP_Post|array<mixed>|null */
        $post = get_post($eventId);

        if ($post instanceof \WP_Post && StagingHelper::CUSTOM_POST_TYPE === $post->post_type) {
            $result = $post;
        }

        return $result;
    }

    /**
     * Fetch the post and Event objects from the database.
     *
     * @param int $eventId The event identifier
     *
     * @return array<mixed> Always returns an array of two elements where the first
     *      elements is the \WP_Post object and the second is the Element
     *      object. In the case where the event does not exist both elements
     *      will be null.
     */
    protected function fetchPostAndEvent(int $eventId): array
    {
        $result = array(null, null);    // Always return 2 elements

        $post = $this->fetchEventPost($eventId);

        if (null != $post) {
            /** @var Event */
            $event = new Event();
            $this->mapper->mapObjectFromString($post->post_content, $event);

            $result = array($post, $event);
        }

        return $result;
    }
}
