<?php

namespace MGGFLOW\DataDealer;

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


    public function __construct(ParserHandler $parserHandler, RegularsData $regularsData)
    {
        $this->parserHandler = $parserHandler;
        $this->regularsData = $regularsData;
    }

    public function handle(object $origin, object $page): array
    {
        $this->url = $page->url; //!!!!!
        // find origin regulars
        $this->getOriginRegulars($origin->id);
        $this->checkRegularsExistence();
        // unique   regulars hash table [hash => expression]
        $this->makeRegularsUnique();
        //парсим
        $this->parse();

        //результаты разделяем на ссылки и целевые вхождения
        //ссылки валидируем, в первую очередь на предмет внутренних и внешних
        //затем ссылки сохраняем в бд
        // !!!результаты, убираем дубликаты
        //затем результаты сохраняем в бд тоже
        //формируем объект результатов

        return ['THIS IS DEBUG FIX IT IN RETURN'];
    }

    private function getOriginRegulars(int $originId)
    {
        $this->originRegulars = $this->regularsData->findOriginRegulars($originId);
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
            $this->uniqueRegulars[md5($regex)] = $regex;
        }
    }

    private function parse()
    {
        $this->parseResult = $this->parserHandler->parse($this->url, $this->uniqueRegulars);
    }


}