<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\UriTemplate\UriTemplate;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RCS\Json\JsonClientTrait;
use WSCL\Main\WsclMainOptionsInterface;
use WSCL\Main\RaceResult\Entity\Event;
use WSCL\Main\RaceResult\Entity\EventSettings;
use WSCL\Main\RaceResult\Entity\RiderTimingData;
use WSCL\Main\RaceResult\Entity\SeasonPoints;
use WSCL\Main\RaceResult\Entity\TeamScoringData;
use WSCL\Main\RaceResult\Filter\RaceResultAuthFilter;
use WSCL\Main\RaceResult\Json\RaceResultFactoryRegistry;

class RaceResultClient
{
    use JsonClientTrait;

    private const API_RACERESULT_URL = 'https://api.raceresult.com';
    private const EVENTS_RACERESULT_URL = 'https://events.raceresult.com';
    private const DATA_API_KEY = 'CQP59JC7AYSOF0A9WRWWPS88G3FET33D';
    private const DATA_API_URL_PREFIX = '/{eventId}/' . self::DATA_API_KEY;

    private const CACHE_TTL = 10 * 60;  // 10 minutes

    private const USER_AGENT_PARTS = array (
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'AppleWebKit/605.1.15 (KHTML, like Gecko)',
        'Chrome/140.0.7339.186'
    );

    private LoggerInterface $logger;

    private Client $apiClient;
    private Client $eventsClient;

    public function __construct(
        WsclMainOptionsInterface $options,
        CacheInterface $cache,
        LoggerInterface $logger
        )
    {
        $this->initJsonClientTrait($cache, self::CACHE_TTL, RaceResultFactoryRegistry::withPhpClassesAdded(true));

        $this->logger = $logger;

        $httpLogger = null;
//             \GuzzleHttp\Middleware::log(
//                 $this->logger,
//                 new \GuzzleHttp\MessageFormatter(MessageFormatter::DEBUG),
//                 'debug'
//                 );

        $this->apiClient = self::getApiClient(
            self::API_RACERESULT_URL,
            [
//                $httpLogger
            ]
            );

        $this->eventsClient = self::getEventsClient(
            $options->getRaceResultUsername(),
            $options->getRaceResultPassword(),
            $httpLogger,
            $this->logger
            );
    }

    /**
     *
     * @param string $baseUri
     * @param callable[] $middleware
     * @param array<string, string> $headers
     *
     * @return Client
     */
    private static function getApiClient(
        string $baseUri,
        array $middleware = [],
        array $headers = []
        ): Client
    {
        $cookieJar = new CookieJar();

        $stack = HandlerStack::create();

        $client = new Client(
            [
                'base_uri' => $baseUri,
                'handler' => $stack,
                RequestOptions::VERIFY => true,
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::COOKIES => $cookieJar,
                RequestOptions::DECODE_CONTENT => 'gzip, deflate, br',
                RequestOptions::HEADERS =>
                    array_merge(
                        [
                            'User-Agent' => sprintf('"%s"', join(' ', self::USER_AGENT_PARTS))
                        ],
                        $headers
                        )
            ],
            );

        foreach($middleware as $entry) {
            $stack->push($entry);
        }

        return $client;
    }


    private static function getEventsClient(string $username, string $password, ?callable $httpLogger, LoggerInterface $logger): Client
    {
        $cookieJar = new CookieJar();

        $eventsApiStack = HandlerStack::create();

        $client = new Client(
            array(
                'base_uri' => self::EVENTS_RACERESULT_URL,
                'handler' => $eventsApiStack,
                RequestOptions::VERIFY => true,
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::COOKIES => $cookieJar,
                RequestOptions::DECODE_CONTENT => 'gzip, deflate, br',
                RequestOptions::HEADERS => array(
                    'User-Agent' => sprintf('"%s"', join(' ', self::USER_AGENT_PARTS)),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
//                    'Referer' => $referer
                ),
            )
            );

        $eventsApiStack->push(new RaceResultAuthFilter($client, $cookieJar, $username, $password, $logger));

        if (isset($httpLogger)) {
            $eventsApiStack->push($httpLogger);
        }

        return $client;
    }

    public static function isValidConfiguration(string $rrUsername, string $rrPassword, LoggerInterface $logger): bool
    {
        $result = false;

        $client = self::getEventsClient($rrUsername, $rrPassword, null, $logger);

        $reqParams = [
            'AddSettings' => implode(',', Event::getAdditionalFields()),
            'year' => 2023
        ];

        $resp = $client->get(
            'api/public/eventlist',
            [
                RequestOptions::QUERY => $reqParams
            ]
            );

        if ($resp->getStatusCode() == 200) {
            $json = (string) $resp->getBody();

            if (!empty($json)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Fetch the events from RaceResult.
     *
     * @return NULL|Event[]
     */
    public function getEvents(?int $year = null): ?array
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, $year ?? '');
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $reqParams = [
                'AddSettings' => implode(',', Event::getAdditionalFields()),
            ];

            if (isset($year)) {
                $reqParams['year'] = $year;
            }

            $resp = $this->eventsClient->get(
                'api/public/eventlist',
                [
                    RequestOptions::QUERY => $reqParams
                ]
            );

            if ($resp->getStatusCode() == 200) {
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new Event(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }

    /**
     * Fetch the settings for a RaceResult event.
     *
     * @return EventSettings|NULL
     */
    public function getEventSettings(int $eventId): ?EventSettings
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, strval($eventId));
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->eventsClient->get(
                UriTemplate::expand('_{eventId}/api/settings/getsettings', array('eventId' => $eventId)),
                [
                    RequestOptions::QUERY => [
                        'names' => implode(',', EventSettings::getJsonFields()),
                    ]
                ]
                );

            if ($resp->getStatusCode() == 200) {
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new EventSettings(),
                    $cacheKey
                    );

                if (is_object($result)) {
                    $result->setId($eventId);
                }
            }
        }

        return $result;
    }

    /**
     * Fetch the seasons points for a particular event.
     *
     * @param int $eventId The event id to retrieve points for.
     *
     * @return SeasonPoints[]|NULL
     */
    public function fetchSeasonPoints(int $eventId): ?array
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, strval($eventId));
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->apiClient->get(
                UriTemplate::expand(self::DATA_API_URL_PREFIX, array('eventId' => $eventId)),
                array(
                    RequestOptions::QUERY => array(
                        'fields' => implode(',', SeasonPoints::getJsonFields()),
                        'listFormat' => 'JSON'
                    )
                )
            );

            if ($resp->getStatusCode() == 200) {
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new SeasonPoints(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }

    /**
     * Fetch the timing statistics from RaceResult for an event.
     *
     * @param int $eventId A RaceResult identifier
     *
     * @return RiderTimingData[]|NULL An array of RiderTimingData or null if an error occurs
     */
    public function fetchRiderTimingStats(int $eventId)
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, strval($eventId));
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $fields = RiderTimingData::getJsonFields();
            $filter = 'OverallRank>0';
            $sortOrder = array(
                'Contest.SortOrder',
                'Status',
                'OverallRank'
            );

            $resp = $this->apiClient->get(
                UriTemplate::expand(self::DATA_API_URL_PREFIX, array('eventId' => $eventId)),
                array(
                    RequestOptions::QUERY => array(
                        'fields' => implode(',', $fields),
                        'filter' => $filter,
                        'sort' => implode(',', $sortOrder),
                        'listFormat' => 'JSON'
                    )
                )
            );

            if ($resp->getStatusCode() == 200) {
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new RiderTimingData(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }

    /**
     * Fetch the team scoring statistics from RaceResult for an event.
     *
     * @param int $eventId A RaceResult identifier
     *
     * @return TeamScoringData[]|NULL An array of TeamScoringData or null if an error occurs
     */
    public function fetchTeamScoringStats(int $eventId)
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, strval($eventId));
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $fields = TeamScoringData::getJsonFields();
            $filter = 'TS_Top3_CurrentRace.Rank>0 OR TS_Top5_CurrentRace.Rank > 0';

            $resp = $this->apiClient->get(
                UriTemplate::expand(self::DATA_API_URL_PREFIX, array('eventId' => $eventId)),
                array(
                    RequestOptions::QUERY => array(
                        'fields' => implode(',', $fields),
                        'filter' => $filter,
                        'listFormat' => 'JSON'
                    )
                )
                );

            if ($resp->getStatusCode() == 200) {
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new TeamScoringData(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }
}
