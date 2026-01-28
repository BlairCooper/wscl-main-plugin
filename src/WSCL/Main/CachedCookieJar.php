<?php
declare(strict_types = 1);
namespace WSCL\Main;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Psr\SimpleCache\CacheInterface;

/**
 * Guzzle CookieJar implementation that stashes the cookies in a cache so
 * they can persist accross PHP requests.
 */
class CachedCookieJar extends CookieJar
{
    public function __construct(
        private CacheInterface $cache,
        private string $cacheKey,
        private int $cacheTTL = 600,
        private bool $storeSessionCookies = false
        )
    {
        parent::__construct();

        $this->load();
    }

    public function __destruct()
    {
        $this->save();
    }

    private function load(): void
    {
        $cookies = $this->cache->get($this->cacheKey);

        if ($cookies) {
            if (\is_array($cookies)) {
                foreach ($cookies as $cookie) {
                    $this->setCookie(new SetCookie($cookie));
                }
            } elseif (\is_scalar($cookies) && !empty($cookies)) {
                throw new \RuntimeException("Invalid cached cookie data: {$cookies}");
            }
        }
    }

    /**
     * Stash the cookies in the cache
     */
    private function save(): void
    {
        $cookies = [];

        /** @var SetCookie $cookie */
        foreach ($this as $cookie) {
            if (CookieJar::shouldPersist($cookie, $this->storeSessionCookies)) {
                $cookies[] = $cookie->toArray();
            }
        }

        $this->cache->set($this->cacheKey, $cookies, $this->cacheTTL);
    }
}
