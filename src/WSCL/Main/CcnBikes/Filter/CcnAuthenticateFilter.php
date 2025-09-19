<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Filter;

use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class CcnAuthenticateFilter
{
    const REFERER_HEADER = 'Referer';
    const SET_COOKIE_HEADER = 'Set-Cookie';
    const CSRFTOKEN_COOKIE = 'csrftoken';
    const CSRFTOKEN_HEADER = 'X-Csrftoken';

    private string $csrfToken;

    public function __construct(
        private Client $client,
        private CookieJar $cookieJar,
        private string $username,
        private string $password,
        private ?LoggerInterface $logger = null
        )
    {
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
        $token = $request->getHeader(self::CSRFTOKEN_HEADER);

        if (empty($token)) {
            if (!isset($this->csrfToken)) {
                $this->acquireCsrfToken($request);
            }
            // Include any cookies we've aquired
            $request = $this->cookieJar->withCookieHeader($request);

            if (isset($this->csrfToken)) {
                $request = $request->withHeader(self::CSRFTOKEN_HEADER, $this->csrfToken);
            }
        }

        return $request;
    }

    private function acquireCsrfToken(RequestInterface $orgReq): void
    {
        /** @var \Psr\Http\Message\ResponseInterface */
        $resp = $this->client->post(
            'users/login/',
            array (
                'json' => array(
                    'username' => $this->username,
                    'password' => $this->password
                ),
                // We'll use the default handler so we don't rerun our middleware
                'handler' => Utils::chooseHandler()
            )
            );

        if ($resp->getStatusCode() == 200 && $resp->hasHeader(self::SET_COOKIE_HEADER)) {
            $this->cookieJar->extractCookies($orgReq, $resp);

            /** @var \GuzzleHttp\Cookie\SetCookie|NULL */
            $setCookie = $this->cookieJar->getCookieByName(self::CSRFTOKEN_COOKIE);

            if ($setCookie) {
                $this->csrfToken = $setCookie->getValue();
            }
/*
            $cookies = $resp->getHeader(self::SET_COOKIE_HEADER);
            array_walk($cookies, fn($cookie) => {
                $this->cookieJar->


            });
            $cookies = array_filter($cookies, fn($entry) => str_starts_with($entry, self::CSRFTOKEN_COOKIE));

            if (!empty($cookies)) {
                $parts = explode(self::CSRFTOKEN_COOKIE.'=', $cookies[0]);
                $parts = explode('; ', $parts[1]);
                $value = $parts[0];

                $this->csrfToken = $value;
                $this->client->
            }
*/
        } else {
            if (!is_null($this->logger)) {
                $this->logger->critical(
                    'Unable to authenticate with CCN: ' . $resp->getStatusCode() . ' / ' . $resp->getBody()
                    );
            }
        }
    }
}
