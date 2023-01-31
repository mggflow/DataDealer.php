<?php

namespace MGGFLOW\DataDealer;

class CorrectPageUrl
{
    private string $formattedUrl;

    public function correct(string $url): string
    {
        $this->removeUrlAnchor();
        return $this->formattedUrl;
    }

    protected function removeUrlAnchor(): string
    {
        $formattedUrl = parse_url ($this->url);
        return $formattedUrl['scheme'].'://'.(isset($formattedUrl['user']) ? $formattedUrl['user'].':'.''
                .$formattedUrl['pass'].'@' : '').$formattedUrl['host'].(isset($formattedUrl['port']) ? ':'
                .$formattedUrl['port'] : '').$formattedUrl['path'].(isset($formattedUrl['query']) ? '?'
                .$formattedUrl['query'] : '');
    }
}