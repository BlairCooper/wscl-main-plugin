<?php
declare(strict_types = 1);
namespace WSCL\Main\Usac;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RCS\Json\JsonClientTrait;
use WSCL\Main\Usac\Entity\SearchResult;

class UsacClient
{
    use JsonClientTrait;

    private const CACHE_TTL = 600;

    private Client $client;

    public function __construct(
        CacheInterface $cache,
        private LoggerInterface $logger
    )
    {
        $this->initJsonClientTrait($cache, self::CACHE_TTL);

        $this->client = new Client([
            'base_url' => 'https://usacycling.sport80.com/',
            RequestOptions::VERIFY => true,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::DECODE_CONTENT => 'gzip, deflate',
            RequestOptions::HEADERS => array(
//                'User-Agent' => sprintf('"%s"', join(' ', self::USER_AGENT_PARTS)),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            )
        ]);
    }

    public function isValidLicense(int $license): bool
    {
        $isValid = false;

        $cacheKey = $this->getCacheKey(__FUNCTION__, strval($license));

        /** @var SearchResult */
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->client->get(
                'pub/athletes/results_search',
                [
                    RequestOptions::QUERY => [
                        's' => $license
                    ]
                ]
            );

            if ($resp->getStatusCode() == 200) {
                /** @var SearchResult */
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new SearchResult(),
                    $cacheKey
                    );
            } else {
                $this->logger->error(
                    'Error checking USAC license validity: {code}/{msg}',
                    array (
                        'code' => $resp->getStatusCode(),
                        'msg' => $resp->getReasonPhrase()
                        )
                    );
            }
        }

        if (1 == count($result->entries)) {
            $isValid = true;
        }

        return $isValid;
    }
}