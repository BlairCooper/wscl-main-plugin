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
use WSCL\Main\Staging\Models\Category;

class CategoryController extends StagingRestController
{
    private const CATEGORY_NOT_FOUND = 'Category Not Found';
    private const UNABLE_TO_CREATE_CATEGORY = 'Unable to create category';


    private const CATEGORIES_ROUTE = '/events/(?P<eventId>[\d]+)/categories';
    private const CATEGORY_ROUTE = self::CATEGORIES_ROUTE.'/(?P<catId>[\d]+)';

    private int $nextCatId = 1;

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
                \stdClass $json,            // NOSONAR - ignore unused parameter
                ObjectWrapper $objWrapper,
                PropertyMap $propMap,       // NOSONAR - ignore unused parameter
                JsonMapperInterface $mapper // NOSONAR - ignore unused parameter
                ) {
                if ($objWrapper->getObject() instanceof Category) {
                    /** @var Category */
                    $category = $objWrapper->getObject();
                    if (null === $category->getId() || $category->getId() <= 0) {
                        $category->setId($this->nextCatId++);
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

        $this->addEndpoint(self::CATEGORIES_ROUTE, $this, 'getCategories', 'GET');
        $this->addEndpoint(self::CATEGORIES_ROUTE, $this, 'createCategory', 'POST');
        $this->addEndpoint(self::CATEGORY_ROUTE, $this, 'getCategory', 'GET');
        $this->addEndpoint(self::CATEGORY_ROUTE, $this, 'updateCategory', 'PUT');
        $this->addEndpoint(self::CATEGORY_ROUTE, $this, 'deleteCategory', 'DELETE');
    }


    /**
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Controllers\StagingRestController::getItemRoute()
     */
    public function getItemRoute(): string
    {
        return self::CATEGORY_ROUTE;
    }

    /**
     * REST endpoint handler to return list of categories for an events.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getCategories(\WP_REST_Request $request): \WP_REST_Response|\WP_Error   // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);

        $event = $this->fetchEvent($eventId);

        if (null !== $event) {
            $result = $event->categories;
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to return a specific category.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getCategory(\WP_REST_Request $request): \WP_REST_Response|\WP_Error // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);
        $catId = intval($request['catId']);

        list ($post, $event) = $this->fetchPostAndEvent($eventId);
        if (null != $post) {
            $category = $event->getCategory($catId);
            if (null !== $category) {
                $result = $category;
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::CATEGORY_NOT_FOUND, array ('status' => 404));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to create a category for an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function createCategory(\WP_REST_Request $request): \WP_REST_Response|\WP_Error  // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);

        $jsonPayload = (string)$request->get_body();

        /** @var Category */
        $category = new Category();
        $this->mapper->mapObjectFromString($jsonPayload, $category);

        list ($post, $event) = $this->fetchPostAndEvent($eventId);

        if (null !== $post) {
            if ($event->addCategory($category)) {

                $post->post_content = json_encode($event);
                if (wp_update_post($post) == $eventId) {
                    $result = $category;
                } else {
                    $result = new \WP_Error(
                        self::INTERNAL_ERROR,
                        self::UNABLE_TO_CREATE_CATEGORY,
                        array ('status' => 400)
                        );
                }
            } else {
                $result = new \WP_Error(self::DATA_ERROR, 'Category already exists', array('status' => 400));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to update a category for an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function updateCategory(\WP_REST_Request $request): \WP_REST_Response|\WP_Error  // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);
        $catId = intval($request['catId']);

        $jsonPayload = (string)$request->get_body();

        /** @var Category */
        $category = new Category();
        $this->mapper->mapObjectFromString($jsonPayload, $category);

        list ($post, $event) = $this->fetchPostAndEvent($eventId);
        if (null != $post) {
            if ($event->updateCategory($catId, $category)) {

                $post->post_content = json_encode($event);
                if (wp_update_post($post) == $eventId) {
                    $result = $category;
                } else {
                    $result = new \WP_Error(
                        self::INTERNAL_ERROR,
                        self::UNABLE_TO_CREATE_CATEGORY,
                        array ('status' => 400)
                        );
                }
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::CATEGORY_NOT_FOUND, array ('status' => 404));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }


    /**
     * REST endpoint handler to delete a category from an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function deleteCategory(\WP_REST_Request $request): \WP_REST_Response|\WP_Error  // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);
        $catId = intval($request['catId']);

        list($post, $event) = $this->fetchPostAndEvent($eventId);

        if (null !== $post) {
            $category = $event->deleteCategory($catId);

            if (null !== $category) {
                $post->post_content = json_encode($event);
                if (wp_update_post($post) == $eventId) {
                    $result = $category;
                } else {
                    $result = new \WP_Error(
                        self::INTERNAL_ERROR,
                        self::UNABLE_TO_CREATE_CATEGORY,
                        array ('status' => 400)
                        );
                }
            } else {
                $result = new \WP_Error(self::NOT_FOUND, self::CATEGORY_NOT_FOUND, array ('status' => 404));
            }
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }
}
