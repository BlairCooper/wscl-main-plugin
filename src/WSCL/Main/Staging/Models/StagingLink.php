<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Models;

class StagingLink
{
    public string $file;
    public string $link;
    public string $mimeType;

    public function __construct(string $file, string $link, string $mimeType)
    {
        $this->file = $file;
        $this->link = $this->parseUrl(parse_url($link));
        $this->mimeType = $mimeType;
    }

    /**
     *
     * @param array<string, mixed> $urlParts
     *
     * @return string
     */
    private function parseUrl(array $urlParts): string
    {
        $scheme   = isset($urlParts['scheme']) ? $urlParts['scheme'] . '://' : '';
        $host     = isset($urlParts['host']) ? $urlParts['host'] : '';
        $port     = isset($urlParts['port']) ? ':' . $urlParts['port'] : '';
        $user     = isset($urlParts['user']) ? $urlParts['user'] : '';
        $pass     = isset($urlParts['pass']) ? ':' . $urlParts['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($urlParts['path']) ? $this->encodePath($urlParts['path']) : '';
        $query    = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
        $fragment = isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    private function encodePath(string $path): string
    {
        $pathParts = explode('/', $path);

        $pathParts = array_map(
            fn($value): string => rawurlencode($value),
            $pathParts
            );

        return join('/', $pathParts);
    }
}
