<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Filter;

use Psr\Http\Message\RequestInterface;

class MailerLiteAuthenticateFilter
{
    const APIKEY_HEADER = 'X-MailerLite-ApiKey';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
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
        return $request->withHeader(self::APIKEY_HEADER, $this->apiKey);
    }
}
