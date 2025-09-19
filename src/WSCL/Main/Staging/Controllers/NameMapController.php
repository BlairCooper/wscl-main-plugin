<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Controllers;

use WSCL\Main\Staging\Models\NameMap;
use WSCL\Main\Staging\Models\NameMapEntry;

/**
 * REST API Controller for managing the map of names.
 *
 * Names may appear one way in the registration system but it is
 * desired to have them appear a different way in the timing system. This
 * API provide the means to manage that mapping.
 * <p>
 * The mappings are stored as a WordPress option in JSON format making the
 * storage seamless as it is the same JSON used in the API itself.
 */
class NameMapController extends StagingRestController
{
    private const MAPPING_NOT_FOUND = 'Name mapping not found';

    private const PLURAL_ROUTE = '/namemappings';
    private const SINGULAR_ROUTE = self::PLURAL_ROUTE.'/(?P<entryId>[\d]+)';
    private const TYPE_ROUTE = self::PLURAL_ROUTE.'/(?P<type>[A-Za-z0-9-_]+)';

    protected function initializeInstance(): void
    {
        parent::initializeInstance();

        $this->addEndpoint(self::PLURAL_ROUTE, $this, 'getMappings', 'GET');
        $this->addEndpoint(self::PLURAL_ROUTE, $this, 'createMapping', 'POST');
        $this->addEndpoint(self::SINGULAR_ROUTE, $this, 'getMapping', 'GET');
        $this->addEndpoint(self::SINGULAR_ROUTE, $this, 'updateMapping', 'PUT');
        $this->addEndpoint(self::SINGULAR_ROUTE, $this, 'deleteMapping', 'DELETE');
        $this->addEndpoint(self::TYPE_ROUTE, $this, 'getMappingsByType', 'GET');
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
     * REST endpoint handler to return list of name mappings.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getMappings(\WP_REST_Request $request): \WP_REST_Response|\WP_Error // @phpstan-ignore  missingType.generics
    {
        $map = NameMap::getInstance();

        return rest_ensure_response($map->getAll());
    }

    /**
     * REST endpoint handler to return list of name mappings.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getMappingsByType(\WP_REST_Request $request): \WP_REST_Response|\WP_Error   // @phpstan-ignore  missingType.generics
    {
        $map = NameMap::getInstance();

        return rest_ensure_response($map->findByType($request['type']));
    }

    /**
     * REST endpoint handler to return a name mapping.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function getMapping(\WP_REST_Request $request): \WP_REST_Response|\WP_Error  // @phpstan-ignore  missingType.generics
    {
        $entryId = intval($request['entryId']);

        $map = NameMap::getInstance();

        $mapEntry = $map->findById($entryId);
        if (isset($mapEntry)) {
            $result = $mapEntry;
        } else {
            $result = new \WP_Error(self::NOT_FOUND, self::MAPPING_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to create a name mapping
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function createMapping(\WP_REST_Request $request): \WP_REST_Response|\WP_Error   // @phpstan-ignore  missingType.generics
    {
        $result = false;

        $jsonPayload = (string)$request->get_body();

        /** @var NameMapEntry */
        $mapping = $this->mapper->mapToClassFromString($jsonPayload, NameMapEntry::class);

        if ($mapping->isValid()) {
            $map = NameMap::getInstance();

            $existingEntry = $map->findByName($mapping->getType(), $mapping->getInName());

            if (isset($existingEntry)) {
                $result = new \WP_Error(
                    self::DATA_ERROR,
                    'Cannot create a mapping that already exists',
                    array ('status' => 400)
                    );
            } else {
                $mapping->clearId();    // ensure the id is not set
                $result = $map->add($mapping);
            }
        } else {
            $result = new \WP_Error(
                self::DATA_ERROR,
                'Missing at least one of the required fields',
                array ('status' => 400)
                );

        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to update a mapping.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function updateMapping(\WP_REST_Request $request): \WP_REST_Response|\WP_Error   // @phpstan-ignore  missingType.generics
    {
        $result = false;

        $entryId = intval($request['entryId']);
        $jsonPayload = (string)$request->get_body();

        /** @var NameMapEntry */
        $mapping = $this->mapper->mapToClassFromString($jsonPayload, NameMapEntry::class);

        if (null === $mapping->getId() || $entryId != $mapping->getId()) {
            $result = new \WP_Error(self::NOT_FOUND, self::MAPPING_NOT_FOUND, array ('status' => 404));
        } else {
            $map = NameMap::getInstance();

            $result = $map->update($mapping);

            if (!isset($result)) {
                $result = new \WP_Error(self::NOT_FOUND, self::MAPPING_NOT_FOUND, array('status' => 404));
            }
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to delete a mapping
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function deleteMapping(\WP_REST_Request $request): \WP_REST_Response|\WP_Error   // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $entryId = intval($request['entryId']);

        $map = NameMap::getInstance();

        $result = $map->delete($entryId);

        if (!isset($result)) {
            $result = new \WP_Error(self::NOT_FOUND, self::MAPPING_NOT_FOUND, array('status' => 404));
        }

        return rest_ensure_response($result);
    }

}
