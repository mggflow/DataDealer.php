<?php

namespace MGGFLOW\DataDealer;

use KubAT\PhpSimple\HtmlDomParser;
use MGGFLOW\DataDealer\Entities\Page;

class ParseHtmlPages
{
    protected string $html;
    protected $dom;
    protected string $parentUrl;
    protected NormalizeUrl $normalizer;

    protected string $host;

    protected array $pages;


    public function __construct(string $html, string $parentUrl)
    {
        $this->html = $html;
        $this->parentUrl = $parentUrl;

        $this->dom = HtmlDomParser::str_get_html($this->html);
        $this->host = $this->getHost($this->parentUrl);
        $this->normalizer = new NormalizeUrl($this->parentUrl);
    }

    public function parse(): array
    {
        $this->pages = [];
        if (empty($this->dom)) return $this->pages;
        foreach ($this->dom->find('a') as $element)
            $this->handleUrl($element->href);

        return $this->pages;
    }

    private function handleUrl($url)
    {
        $url = $this->normalizer->norm($url);

        if ($this->isPageUrl($url)) {
            $this->pages[] = $url;
        }
    }


    private function isPageUrl(string $url): bool
    {
        $ext = $this->getExt($url);

        return in_array($ext, Page::PAGE_EXTENSIONS);
    }

    private function getHost($url)
    {
        return (parse_url($url, PHP_URL_HOST) ?? '');
    }

    private function getPath($url)
    {
        return (parse_url($url, PHP_URL_PATH) ?? '');
    }

    private function getExt($url): string
    {
        $path = $this->getPath($url);

        return mb_strtolower(mb_substr(mb_strrchr($path, '.'), 1));
    }
}