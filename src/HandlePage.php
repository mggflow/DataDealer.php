<?php

namespace MGGFLOW\DataDealer;

use MGGFLOW\DataDealer\Entities\Match;
use MGGFLOW\DataDealer\Entities\Page;
use MGGFLOW\DataDealer\Entities\Regular;
use MGGFLOW\DataDealer\Exceptions\RegularsNotFound;
use MGGFLOW\DataDealer\Interfaces\PageHandler;
use MGGFLOW\DataDealer\Interfaces\ParserHandler;
use MGGFLOW\DataDealer\Interfaces\RegularsData;

class HandlePage implements PageHandler
{
    private ParserHandler $parserHandler;
    private RegularsData $regularsData;

    private object $origin;
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
    protected array $pagesUrls;


    public function __construct(ParserHandler $parserHandler, RegularsData $regularsData)
    {
        $this->parserHandler = $parserHandler;
        $this->regularsData = $regularsData;
    }

    public function handle(object $origin, object $page): array
    {
        $this->setFields($origin, $page);
        $this->getOriginRegulars();
        $this->checkRegularsExistence();
        $this->genUrlsExpressionHash();
        $this->makeRegularsUnique(); //Сделать отдельный класс для хеширования
        $this->parse();
        $this->genContentHash();
        if ($this->pageHasSameContent()){
            // return empty result
        }
        $this->applyRegexToParsingResult();
        $this->distributeMatches();


        //ссылки валидируем, в первую очередь на предмет внутренних и внешних
        //затем ссылки сохраняем в бд
        // !!!результаты, убираем дубликаты
        //затем результаты сохраняем в бд тоже
        //формируем объект результатов

        return ['THIS IS DEBUG FIX IT IN RETURN'];
    }

    private function setFields(object $origin, object $page)
    {
        $this->origin = $origin;
        $this->pageUrl = $page->url;
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

    protected function genUrlsExpressionHash() {
        $this->urlsExpressionHash = HashString::hash(Regular::URL_REGEX);
    }

    private function makeRegularsUnique()
    {
        $this->uniqueRegulars[$this->urlsExpressionHash] = [
            'id' => 0,
            'expression' => Regular::URL_REGEX
        ];

        foreach ($this->originRegulars as $regular){
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

    protected function genContentHash() {
        $this->contentHash = HashString::hash($this->parsingResult['html']);
    }

    protected function pageHasSameContent(): bool {
        return $this->contentHash == $this->pagePrevContentHash;
    }

    private function applyRegexToParsingResult()
    {
        $search = new ApplyRegularsToPage();
        $this->regularsApplyingResult = $search->apply($this->uniqueRegulars, $this->parsingResult);
    }

    protected function distributeMatches() {
        $this->matchesToAdd = [];
        foreach ($this->regularsApplyingResult['matches'] as $this->regularHash=>$this->regularMatches) {
            if($this->isNotOnlyEntityRegular()){
                $this->takeMatchesValues();
            }

            if($this->isUrlMatches()){
                $this->takePageUrls();
            }
        }
    }

    protected function isNotOnlyEntityRegular(): bool {
        return $this->uniqueRegulars[$this->regularHash]['id'] != 0;
    }

    protected function takeMatchesValues(){
        foreach ($this->regularMatches as $matchValue){
            if (strlen($matchValue) > Match::MAX_VALUE_LENGTH) continue;

            $this->matchesToAdd[] = [
                'origin_id' => $this->origin,
                'regular_id' => $this->uniqueRegulars[$this->regularHash]['id'],
                'value' => $matchValue,
                'value_hash' => HashString::hash($matchValue),
                'created_at' => time()
            ];
        }
        // save matches
    }

    protected function isUrlMatches(): bool
    {
        return $this->regularHash == $this->urlsExpressionHash;
    }

    protected function takePageUrls() {
        foreach ($this->regularMatches as $matchPageUrl){
            if (parse_url($matchPageUrl, PHP_URL_HOST) != $this->origin->host) continue;

            $path = parse_url($matchPageUrl, PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (!in_array($extension, Page::ALLOWABLE_URL_PATH_EXTENSIONS)) continue;

        }
    }

    protected function isValidPageUrl(): bool {

    }


}



