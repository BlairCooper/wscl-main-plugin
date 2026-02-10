<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Controllers;

use RCS\Logging\InMemoryLogger;
use WSCL\Main\Staging\StagingApp;
use Psr\Log\LoggerInterface;
use RCS\WP\PluginInfoInterface;
use WSCL\Main\WsclMainOptionsInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use WSCL\Main\RaceResult\RaceResultClient;

class StagingController extends StagingRestController
{
    private const STAGING_ROUTE = '';

    private const PARAM_EVENT_ID = 'eventId';
    private const PARAM_REGISTRATION_FILE = 'regFile';

    public function __construct(
        PluginInfoInterface $pluginInfo,
        int $apiVersion,
        string $apiRoute,
        WsclMainOptionsInterface $options,
        private RaceResultClient $rrClient,
        private BgProcessInterface $bgProcess,
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

        $this->addEndpoint(self::STAGING_ROUTE, $this, 'runStaging', 'POST');
    }

    public function getItemRoute(): string
    {
        return self::STAGING_ROUTE;
    }

    /**
     * REST endpoint handler to generation the staging reports.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function runStaging(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $result = false;

        $logger = new InMemoryLogger();

        try {
            $this->validateEventParam($request->get_param(self::PARAM_EVENT_ID));
            $this->validateFilesParam($request->get_file_params());

            $files = $request->get_file_params();

            $stagingApp = new StagingApp($this->pluginInfo, $this->options, $this->rrClient, $logger, $this->bgProcess);

            $outputFiles = $stagingApp->generateStaging(
                $this->fetchEvent(intval($request->get_param(self::PARAM_EVENT_ID))),
                $files[self::PARAM_REGISTRATION_FILE]['tmp_name']
                );

            $logMsgs = $logger->getLogMsgs();

            if (empty($logMsgs)) {
                $result = new \WP_REST_Response($outputFiles);
            } else {
                throw new \DomainException('Errors processing staging.', 400);
            }

            // Dispatch any background tasks that were added
            $this->bgProcess->save();
            $this->bgProcess->dispatch();
        } catch (\DomainException $de) {
            $result = new \WP_Error(
                self::DATA_ERROR,
                'Invalid data: ' . $de->getMessage(),
                array ('status' => $de->getCode(), 'logMsgs' => $logger->getLogMsgs())
                );
        } catch (\InvalidArgumentException $iae) {
            $result = new \WP_Error(
                self::PARAMETER_ERROR,
                'Invalid argument: ' . $iae->getMessage(),
                array ('status' => $iae->getCode(), 'logMsgs' => $logger->getLogMsgs())
                );
        }

        return rest_ensure_response($result);
    }

    private function validateEventParam(?string $eventId): void
    {
        if (is_null($eventId)) {
            throw new \InvalidArgumentException('Missing eventId parameter', 400);
        }

        if (!is_numeric($eventId)) {
            throw new \InvalidArgumentException('EventId parameter must be a number', 400);
        }

        $event = $this->fetchEvent(intval($eventId));

        if (is_null($event)) {
            throw new \InvalidArgumentException('EventId parameter is not a valid event identifier', 400);
        }
    }

    /**
     *
     * @param array<string, array<string, string>> $files
     */
    private function validateFilesParam(array $files): void
    {
        if (empty($files)) {
            throw new \InvalidArgumentException('Missing input files', 400);
        }

        $missingFiles = array_diff(
            array(
                self::PARAM_REGISTRATION_FILE),
            array_keys($files)
            );
        if (!empty($missingFiles)) {
            throw new \InvalidArgumentException('Missing input files: ' . implode(', ', $missingFiles));
        }

        foreach ($files as $file) {
            if ('text/csv' <=> $file['type']) {
                throw new \InvalidArgumentException('File must be in CSV format: '. $file['name']);
            }
        }
    }
}
