<?php

namespace MGGFLOW\DataDealer;

use MGGFLOW\DataDealer\Entities\Match;
use MGGFLOW\DataDealer\Entities\Page;
use MGGFLOW\DataDealer\Entities\Regular;
use MGGFLOW\DataDealer\Exceptions\ParsingFailed;
use MGGFLOW\DataDealer\Exceptions\RegularsNotFound;
use MGGFLOW\DataDealer\Interfaces\MatchData;
use MGGFLOW\DataDealer\Interfaces\PageData;
use MGGFLOW\DataDealer\Interfaces\PageHandler;
use MGGFLOW\DataDealer\Interfaces\ParserHandler;
use MGGFLOW\DataDealer\Interfaces\RegularsData;

class HandlePage implements PageHandler
{
    private ParserHandler $parserHandler;
    private RegularsData $regularsData;
    private MatchData $matchData;
    private PageData $pageData;

    private object $origin;
    protected int $pageId;
    private string $pageUrl;
    protected string $pagePrevContentHash;

    private array $parsingResult;
    protected string $contentHash;
    private array $originRegulars;
    protected string $urlsExpressionHash;
    private array $uniqueRegulars;

    protected array $regularsApplyingResult;

    protected string $regularHash;
    protected array $regularMatches;

    protected array $matchesToAdd;
    protected array $pagesUrlCandidates;
    protected array $pagesToAdd;

    protected ?array $matchesSavingResult;
    protected ?array $pagesSavingResult;


    public function __construct(ParserHandler $parserHandler, RegularsData $regularsData,
                                MatchData     $matchData, PageData $pageData)
    {
        $this->parserHandler = $parserHandler;
        $this->regularsData = $regularsData;
        $this->matchData = $matchData;
        $this->pageData = $pageData;
    }

    public function handle(object $origin, object $page): array
    {
        $this->initFields($origin, $page);
        $this->getOriginRegulars();
        $this->checkRegularsExistence();
        $this->genUrlsExpressionHash();
        $this->makeRegularsUnique();
        $this->parse();
        $this->checkParsingResult();
        $this->genContentHash();
        if ($this->pageHasSameContent()) {
            return $this->createSummary();
        }
        $this->refreshPageContentHash();
        $this->applyRegexToParsingResult();
        $this->distributeMatches();
        $this->parsePagesDirectly();
        $this->addPageNotes();

        return $this->createSummary();
    }

    private function initFields(object $origin, object $page)
    {
        $this->origin = $origin;
        $this->pageId = $page->id;
        $this->pageUrl = $page->url;
        $this->pagePrevContentHash = $page->content_hash;
        $this->matchesSavingResult = null;
        $this->pagesSavingResult = null;
    }

    private function getOriginRegulars()
    {
        $this->originRegulars = $this->regularsData->findOriginRegulars($this->origin->id);
    }

    private function checkRegularsExistence()
    {
        if (empty($this->originRegulars)) {
            throw new RegularsNotFound();
        }
    }

    protected function genUrlsExpressionHash()
    {
        $this->urlsExpressionHash = HashString::hash(Regular::URL_REGEX);
    }

    private function makeRegularsUnique()
    {
        $this->uniqueRegulars[$this->urlsExpressionHash] = [
            'id' => 0,
            'expression' => Regular::URL_REGEX
        ];

        foreach ($this->originRegulars as $regular) {
            if (is_array($regular)) $regular = (object)$regular;
            $this->uniqueRegulars[$regular->expression_hash] = [
                'id' => $regular->id,
                'expression' => $regular->expression,
            ];
        }
    }

    private function parse()
    {
        $this->parsingResult = $this->parserHandler->parse($this->pageUrl);
    }

    protected function checkParsingResult()
    {
        if (!isset($this->parsingResult['html'])) throw new ParsingFailed();
    }

    protected function genContentHash()
    {
        $this->contentHash = HashString::hash($this->parsingResult['html']);
    }

    protected function pageHasSameContent(): bool
    {
        return $this->contentHash == $this->pagePrevContentHash;
    }

    protected function refreshPageContentHash()
    {
        $this->pageData->updateContentHash($this->pageId, $this->contentHash);
    }

    private function applyRegexToParsingResult()
    {
        $search = new ApplyRegularsToPage();
        $this->regularsApplyingResult = $search->apply($this->uniqueRegulars, $this->parsingResult);
    }

    protected function distributeMatches()
    {
        $this->pagesToAdd = [];
        $this->matchesToAdd = [];
        $this->matchesSavingResult = [];
        foreach ($this->regularsApplyingResult['matches'] as $this->regularHash => $this->regularMatches) {
            if ($this->isNotOnlyEntityRegular()) {
                $this->takeMatchesValues();
            }

            if ($this->isUrlMatches()) {
                $this->addPagesUrlCandidates($this->regularMatches);
            }
        }
    }

    protected function parsePagesDirectly()
    {
        $parser = new ParseHtmlPages($this->parsingResult['html'], $this->pageUrl);
        $candidates = $parser->parse();
        $this->addPagesUrlCandidates($candidates);
    }

    protected function isNotOnlyEntityRegular(): bool
    {
        return $this->uniqueRegulars[$this->regularHash]['id'] != 0;
    }

    protected function takeMatchesValues()
    {
        foreach ($this->regularMatches as $matchValue) {
            if (strlen($matchValue) > Match::MAX_VALUE_LENGTH) continue;

            $this->matchesToAdd[] = [
                'origin_id' => $this->origin->id,
                'regular_id' => $this->uniqueRegulars[$this->regularHash]['id'],
                'value' => $matchValue,
                'value_hash' => HashString::hash($matchValue),
                'created_at' => time()
            ];
        }
        $this->matchesSavingResult[$this->regularHash] = $this->matchData->addAny($this->matchesToAdd);
    }

    protected function isUrlMatches(): bool
    {
        return $this->regularHash == $this->urlsExpressionHash;
    }

    protected function addPagesUrlCandidates(array &$urls)
    {
        foreach ($urls as $url) {
            $this->pagesUrlCandidates[] = $url;
        }
    }

    protected function addPageNotes()
    {
        $correctPageUrl = new CorrectPageUrl();

        foreach ($this->pagesUrlCandidates as &$pageUrlCandidate) {
            $pageUrlCandidate = $correctPageUrl->correct($pageUrlCandidate);
        }

        $this->pagesUrlCandidates = array_unique($this->pagesUrlCandidates);

        foreach ($this->pagesUrlCandidates as $pageUrl) {
            $this->preparePageNote($pageUrl);
        }
        if (empty($this->pagesToAdd)) return;

        $this->pagesSavingResult[] = $this->pageData->addAny($this->pagesToAdd);
    }

    protected function isValidPageUrl(string $matchPageUrl): bool
    {
        if (strlen($matchPageUrl) > Page::MAX_PAGE_URL_LENGTH) return false;

        if (mb_strtolower((parse_url($matchPageUrl, PHP_URL_HOST) ?? '')) != $this->origin->host) return false;

        return true;
    }

    protected function preparePageNote(string $correctedUrl)
    {
        if (!$this->isValidPageUrl($correctedUrl)) return;

        $this->pagesToAdd[] = [
            'origin_id' => $this->origin->id,
            'url' => $correctedUrl,
            'url_hash' => HashString::hash($correctedUrl),
            'content_hash' => '',
            'created_at' => time()
        ];
    }

    private function createSummary(): array
    {
        return [
            'matches' => $this->matchesSavingResult,
            'pages' => $this->pagesSavingResult
        ];
    }
}



