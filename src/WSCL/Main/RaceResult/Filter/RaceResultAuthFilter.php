<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Filter;

use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\RequestOptions;

class RaceResultAuthFilter
{
    const REFERER_HEADER = 'Referer';
    const SET_COOKIE_HEADER = 'Set-Cookie';
    const SESSION_ID_PARAM = 'sessid';
    const LANG_PARAM = 'lang';
    const PASSWORD_PARAM = 'pw';
    const COOKIE_PREFIX = 'st_';

    private LoggerInterface $logger;

    private Client $client;
    private CookieJar $cookieJar;
    private string $username;
    private string $password;

    private string $sessionToken;

    public function __construct(Client $client, CookieJar $cookieJar, string $username, string $password, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->cookieJar = $cookieJar;
        $this->username = $username;
        $this->password = $password;

        $this->logger = $logger;
    }

    public function __invoke(callable $next): callable
    {
        return function (RequestInterface $request, array $options = array()) use ($next) {
            $request = $this->applyAuthentication($request);

            return $next($request, $options);
        };
    }

    private function applyAuthentication(RequestInterface $request): RequestInterface
    {
        if (!isset($this->sessionToken) ||
            null == $this->cookieJar->getCookieByName(self::COOKIE_PREFIX . $this->sessionToken)
            ) {
            $this->acquireToken($request);
        }

        $queryParams = [];

        parse_str($request->getUri()->getQuery(), $queryParams);

        $queryParams[self::LANG_PARAM] = 'en';
        $queryParams[self::SESSION_ID_PARAM] = $this->sessionToken;
        $queryParams[self::PASSWORD_PARAM] = $this->sessionToken;

        $queryUri = $request->getUri()->withQuery(http_build_query($queryParams));
        $request = $request->withUri($queryUri);
        $request = $this->cookieJar->withCookieHeader($request);

        return $request;
    }

    private function acquireToken(RequestInterface $orgReq): void
    {
        /** @var \Psr\Http\Message\ResponseInterface */
        $resp = $this->client->post(
            'api/public/login',
            [
                RequestOptions::ALLOW_REDIRECTS => true,
                RequestOptions::QUERY => [
                    'lang' => 'en',
                    'forceNew' => 2
                ],
                RequestOptions::FORM_PARAMS => [
                    'user' => $this->username,
                    'pw' => $this->password
                ],
                // We'll use the default handler so we don't rerun our middleware
                'handler' => Utils::chooseHandler()
            ]
            );

        if ($resp->getStatusCode() == 200 && $resp->hasHeader(self::SET_COOKIE_HEADER)) {
            $this->sessionToken = $resp->getBody()->__toString();

            // Copy the cookies to the cookie jar
            $this->cookieJar->extractCookies($orgReq, $resp);
        } else {
            $this->logger->critical(
                'Unable to authenticate with RaceResult: ' . $resp->getStatusCode() . ' / ' . $resp->getBody()
                );
        }
    }
}
