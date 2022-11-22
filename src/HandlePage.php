<?php

namespace MGGFLOW\DataDealer;

use MGGFLOW\DataDealer\Entities\Regular;
use MGGFLOW\DataDealer\Exceptions\RegularsNotFound;
use MGGFLOW\DataDealer\Interfaces\PageHandler;
use MGGFLOW\DataDealer\Interfaces\ParserHandler;
use MGGFLOW\DataDealer\Interfaces\RegularsData;

class HandlePage implements PageHandler
{
    private ParserHandler $parserHandler;
    private RegularsData $regularsData;

    private array $parseResult;
    private array $originRegulars;
    private array $uniqueRegulars;

    private string $url;
    private int $originId;


    public function __construct(ParserHandler $parserHandler, RegularsData $regularsData)
    {
        $this->parserHandler = $parserHandler;
        $this->regularsData = $regularsData;
    }

    public function handle(object $origin, object $page): array
    {
        $this->setParsingOptions($origin, $page);
        $this->getOriginRegulars();
        $this->checkRegularsExistence();
        $this->makeRegularsUnique();
        $this->parse();

        //результаты разделяем на ссылки и целевые вхождения
        //ссылки валидируем, в первую очередь на предмет внутренних и внешних
        //затем ссылки сохраняем в бд
        // !!!результаты, убираем дубликаты
        //затем результаты сохраняем в бд тоже
        //формируем объект результатов

        return ['THIS IS DEBUG FIX IT IN RETURN'];
    }

    private function setParsingOptions(object $origin, object $page)
    {
        $this->originId = $origin->id;
        $this->url = $page->url;
    }

    private function getOriginRegulars(int $originId)
    {
        $this->originRegulars = $this->regularsData->findOriginRegulars($this->originId);
    }

    private function checkRegularsExistence()
    {
        if (empty($this->originRegulars)) {
            throw new RegularsNotFound();
        }
    }

    private function makeRegularsUnique()
    {
        foreach ($this->originRegulars as $regex)
        {
            $this->uniqueRegulars[$regex->expression_hash] = $regex->expression;
        }

        $this->uniqueRegulars[md5(Regular::URL_REGEX)] = Regular::URL_REGEX;
    }

    private function parse()
    {
        $this->parseResult = $this->parserHandler->parse($this->url, $this->uniqueRegulars);
    }
}



