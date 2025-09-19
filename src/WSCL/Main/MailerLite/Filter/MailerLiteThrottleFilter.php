<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Filter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Guzzle Middleware filter to capture MailerLite headers from responses and
 * use them to throttle requests.
 *
 * Throttling is separate for each endpoint. Different methods (e.g. GET vs
 * PUT) for the same URI are considered different endpoints.
 */
class MailerLiteThrottleFilter
{
    // The maximum number of API requests that the user can make per minute.
    //private const RATE_LIMIT = "X-RateLimit-Limit";

    // The remaining number of API requests that the user can make per minute.
    private const RATE_REMAINING = 'x-ratelimit-remaining';
    // A date and time value indicating when the remaining limit resets.
    private const RATE_RESET = 'x-ratelimit-reset';
    // Indicates the seconds remaining before you can make a new request.
    private const RATE_RETRY_AFTER = 'x-ratelimit-retry-after';

    private const URL_PATH_PATTERN = '/^(.*)\\/[0-9]+$/';

    private RetryDataMap $retryDataMap;


    public function __construct()
    {
        $this->retryDataMap = new RetryDataMap();
    }

    /**
     * Filter a request, checking if we have exceeded the throttling for the
     * endpoint. If we have we will sleep for a bit until the window opens
     * up for additional requests.
     */
    public function __invoke(callable $next): callable
    {
        return function (RequestInterface $request, array $options = array()) use ($next) {
            $request = $this->filterRequest($request, $options);

            /** @var \GuzzleHttp\Promise\PromiseInterface */
            $promise = $next($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($request) {
                $response = $this->filterResponse($request, $response);

                return $response;
            });
        };
    }

    /**
     * Filter a request, checking if we have exceeded the throttling for the
     * endpoint. If we have we will sleep for a bit until the window opens
     * up for additional requests.
     *
     * @param RequestInterface $request The request being filtered
     * @param array<mixed> $options Additional options for the request
     *
     * @return RequestInterface The potentially modified request
     */
    public function filterRequest(RequestInterface $request, array $options = array()): RequestInterface
    {
        /** @var ?RetryData */
        $retryData = $this->retryDataMap->get(self::getEndpointKey($request));

        // If there is an entry for the path, check if we need to delay, otherwise continue with the request
        if (null !== $retryData) {
            // Check if we have run out of requests left and the reset time has not passed
//            $diffInterval = $retryData->retryReset->diff(new \DateTime('now', new \DateTimeZone('GMT')));

            if (0 >= $retryData->rateRemaining &&
                $retryData->retryReset > new \DateTime('now', new \DateTimeZone('GMT'))
//                1 == $diffInterval->invert) // Is after?
                ) {
                // Wait for the suggested delay plus two seconds, then continue
                sleep($retryData->retryAfter + 2);
            } elseif ($retryData->retryReset == new \DateTime('now', new \DateTimeZone('GMT'))) {
                sleep(2);
            }
        }

        return $request;
    }

    /**
     * Filter a response, checking for the header fields used to control
     * request throttling.
     *
     * @param RequestInterface $request The request context for the response
     *         being filtered.
     * @param ResponseInterface $response The response being filtered.
     *
     * @return ResponseInterface The potentially modified response
     */
    public function filterResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $headers = $response->getHeaders();

        if (array_key_exists(self::RATE_REMAINING, $headers) &&
            array_key_exists(self::RATE_RETRY_AFTER, $headers) &&
            array_key_exists(self::RATE_RESET, $headers)
            ) {
            $endpointKey = self::getEndpointKey($request);
            /** @var RetryData */
            $retryData = $this->retryDataMap->computeIfAbsent($endpointKey, new RetryData());

            $retryData->rateRemaining = intval($headers[self::RATE_REMAINING][0]);
            $retryData->retryAfter = intval($headers[self::RATE_RETRY_AFTER][0]);

            $timestamp = \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $headers[self::RATE_RESET][0]);
            if (false !== $timestamp) {
               // include an additional 5 seconds to the retry time as a buffer
               $retryData->retryReset = $timestamp->add(new \DateInterval('PT5S'));
            }
        }

        return $response;
    }

/*
    public function filter(ClientRequestContext reqCtx) throws IOException {
        final RetryData retryData = retryDataMap.get(getEndpointKey(reqCtx));

        // If there is an entry for the path, check if we need to delay, otherwise continue with the request
        if (null != retryData) {
            // Check if we have run out of requests left and the reset time has not passed
            if (0 >= retryData.rateRemaining &&
                retryData.retryReset.isAfter(LocalDateTime.now(ZoneId.of("GMT"))) )
            {
                try {
                    // Wait for the suggested delay plus two seconds, then continue
                    TimeUnit.SECONDS.sleep(retryData.retryAfter + 2L);
                } catch (InterruptedException e) {
                    e.printStackTrace();
                    Thread.currentThread().interrupt();
                }
            }
            else if (retryData.retryReset.isEqual(LocalDateTime.now(ZoneId.of("GMT")))) {
                int x = 1;
                try { TimeUnit.SECONDS.sleep(2); } catch (InterruptedException ie) {Thread.currentThread().interrupt();}
            }
        }
    }
*/

    /**
     * Determine the map key to use for the given request context.
     * <p>
     * This is done by combining the request method and the base URI for the
     * request, e.g. "PUT:/api/v2/subscribers".
     *
     * @param RequestInterface $request The request context for the request or response.
     *
     * @return string A string defining the key for the context.
     */
    private static function getEndpointKey(RequestInterface $request): string
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $matches = array();

        if (preg_match(self::URL_PATH_PATTERN, $path, $matches)) {
            $path = $matches[1];
        }

        return $method . ":" . $path;
    }
}

/**
 * Structure for holding the retry state for any given endpoint.
 *
 */
class RetryData
{
    public int $rateRemaining;
    public int $retryAfter;
    public \DateTime $retryReset;

    public function __construct()
    {
        $this->rateRemaining = 60;
        $this->retryAfter = 0;
        $this->retryReset = new \DateTime();
    }
}

class RetryDataMap
{
    /** @var RetryData[] */
    private $map = [];

    public function get(string $endpointKey): ?RetryData
    {
        $result = null;

        if (array_key_exists($endpointKey, $this->map)) {
            $result = $this->map[$endpointKey];
        }

        return $result;
    }

    public function computeIfAbsent(string $endpointKey, RetryData $ifAbsent): RetryData
    {
        if (!array_key_exists($endpointKey, $this->map)) {
            $this->map[$endpointKey] = $ifAbsent;
        }

        return $this->map[$endpointKey];
    }
}
