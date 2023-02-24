<?php

namespace MGGFLOW\DataDealer;

class NormalizeUrl
{
    protected string $parentUrl;

    public function __construct(string $parentUrl)
    {
        $this->parentUrl = $parentUrl;
    }

    public function norm(string $url): string
    {
        $normalized = $url;
        if ($this->getScheme($url)) {
            return $normalized;
        } elseif ($this->getHost($url)) {
            if (mb_substr($url, 0, 2) == '//') {
                $normalized = $this->getScheme($this->parentUrl) . ':' . $normalized;
            } else {
                $normalized = $this->getScheme($this->parentUrl) . '://' . $normalized;
            }
        } elseif (mb_substr($url, 0, 1) == '#') {
            $normalized = $this->parentUrl . $normalized;
        } elseif (mb_substr($url, 0, 1) != '/') {
            $normalized = rtrim($this->withoutFile($this->parentUrl), '/')
                . '/' . ltrim($normalized, '/');
        } else {
            $normalized = $this->getScheme($this->parentUrl)
                . '://' . $this->getHost($this->parentUrl)
                . '/' . ltrim($normalized, '/');
        }

        return $normalized;
    }

    private function withoutFile($url): string
    {
        $scheme = $this->getScheme($url);
        $host = $this->getHost($url);
        $path = $this->getPath($url);
        preg_match('/.+?\//', $path, $matches);

        $returned = $scheme . '://' . $host;

        if (isset($matches[0])) {
            return $returned . $matches[0];
        } else {
            return $returned;
        }
    }

    private function getScheme(string $url)
    {
        return (parse_url($url, PHP_URL_SCHEME) ?? 'http');
    }

    private function getHost($url)
    {
        return (parse_url($url, PHP_URL_HOST) ?? '');
    }

    private function getPath($url)
    {
        return (parse_url($url, PHP_URL_PATH) ?? '');
    }
}