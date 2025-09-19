<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Controllers;

use Psr\Log\LoggerInterface;
use RCS\WP\PluginInfoInterface;
use WSCL\Main\RaceAnalysis\RiderAnalyzer;
use WSCL\Main\RaceAnalysis\TeamScoringAnalyzer;
use WSCL\Main\RaceResult\RaceResultClient;
use WSCL\Main\RaceResult\Entity\Event;
use WSCL\Main\RaceResult\Entity\RiderTimingData;
use WSCL\Main\Staging\Models\StagingLink;
use WSCL\Main\RaceResult\Entity\TeamScoringData;
use WSCL\Main\WsclMainOptionsInterface;

class AnalyzeEventController extends StagingRestController
{
    private const ROUTE = '/analyze/(?P<eventId>[\d]+)';

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
     *
     * {@inheritDoc}
     * @see \WSCL\Main\Staging\Controllers\StagingRestController::getItemRoute()
     */
    public function getItemRoute(): string
    {
        return self::ROUTE;
    }

    /**
     * {@inheritDoc}
     * @see \RCS\WP\Rest\RestController::initializeInstance()
     */
    protected function initializeInstance(): void
    {
        parent::initializeInstance();

        $this->addEndpoint(self::ROUTE.'/riders', $this, 'analyzeRiders', 'GET');
        $this->addEndpoint(self::ROUTE.'/teamScoring', $this, 'analyzeTeamScoring', 'GET');
    }

    /**
     * REST endpoint handler to return rider analysis for an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function analyzeRiders(\WP_REST_Request $request): \WP_REST_Response|\WP_Error   // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);

        $rrEvents = $this->rrClient->getEvents();

        if (!empty($rrEvents)) {
            $rrEvents = array_filter($rrEvents, fn($event) => $event->getId() == $eventId);

            if (!empty($rrEvents)) {
                /** @var Event */
                $rrEvent = array_shift($rrEvents);

                /** @var RiderTimingData[] */
                $timingStats = $this->rrClient->fetchRiderTimingStats($eventId);

                if (!empty($timingStats)) {
                    $analyzer = new RiderAnalyzer($rrEvent, $this->pluginInfo, $this->options, $this->logger);
                    $analyzer->loadData($timingStats);

                    $pdfFile = $analyzer->generateReport();

                    $link = new StagingLink(
                        'Rider Race Analysis',
                        str_replace(
                            wp_upload_dir()['basedir'],
                            wp_upload_dir()['baseurl'],
                            $pdfFile
                            ),
                        mime_content_type($pdfFile)
                        );

                    $result = new \WP_REST_Response($link);
                }
                else {
                    $result = new \WP_Error(
                        self::NOT_FOUND,
                        'Unable to retrieve timing statistics',
                        array('status' => 404)
                        );
                }
            }
            else {
                $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
            }
        }
        else {
            $result = new \WP_Error(
                self::INTERNAL_ERROR,
                'Unable to retrieve events from RaceResult',
                array('status' => 400)
                );
        }

        return rest_ensure_response($result);
    }

    /**
     * REST endpoint handler to return team scoring analysis for an event.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function analyzeTeamScoring(\WP_REST_Request $request): \WP_REST_Response|\WP_Error  // @phpstan-ignore  missingType.generics
    {
        $result = false;
        $eventId = intval($request['eventId']);

        $rrEvents = $this->rrClient->getEvents();

        if (!empty($rrEvents)) {
            $rrEvents = array_filter($rrEvents, fn($event) => $event->getId() == $eventId);

            if (!empty($rrEvents)) {
                /** @var Event */
                $rrEvent = array_shift($rrEvents);

                /** @var TeamScoringData[] */
                $teamScoring = $this->rrClient->fetchTeamScoringStats($eventId);

                if (!empty($teamScoring)) {
                    $analyzer = new TeamScoringAnalyzer($rrEvent, $this->pluginInfo, $this->options, $this->logger);
                    $analyzer->loadData($teamScoring);

                    $pdfFile = $analyzer->generateReport();

                    $link = new StagingLink(
                        'Team Scoring Analysis',
                        str_replace(
                            wp_upload_dir()['basedir'],
                            wp_upload_dir()['baseurl'],
                            $pdfFile
                            ),
                        mime_content_type($pdfFile)
                        );

                    $result = new \WP_REST_Response($link);
                }
                else {
                    $result = new \WP_Error(
                        self::NOT_FOUND,
                        'Unable to retrieve timing statistics',
                        array('status' => 404)
                        );
                }
            }
            else {
                $result = new \WP_Error(self::NOT_FOUND, self::EVENT_NOT_FOUND, array('status' => 404));
            }
        }
        else {
            $result = new \WP_Error(
                self::INTERNAL_ERROR,
                'Unable to retrieve events from RaceResult',
                array('status' => 400)
                );
        }

        return rest_ensure_response($result);
    }
}
