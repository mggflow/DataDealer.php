<?php

namespace MGGFLOW\DataDealer;

class CorrectPageUrl
{
    public function correct(string $url): string
    {
        return $this->removeUrlAnchor($url);
    }

    protected function removeUrlAnchor(string $url): string
    {
        $formattedUrl = parse_url($url);
        return ($formattedUrl['scheme'] ?? 'http') . '://'
            . (isset($formattedUrl['user']) ? $formattedUrl['user'] . ':' . $formattedUrl['pass'] . '@' : '')
            . $formattedUrl['host']
            . (isset($formattedUrl['port']) ? ':' . $formattedUrl['port'] : '')
            . ($formattedUrl['path'] ?? '')
            . (isset($formattedUrl['query']) ? '?' . $formattedUrl['query'] : '');
    }
}