<?php

namespace MGGFLOW\DataDealer;

use MGGFLOW\DataDealer\Interfaces\PageHandler;
use MGGFLOW\DataDealer\Interfaces\ParserHandler;

class HandlePage implements PageHandler
{
    private ParserHandler $parserHandler;

    private array $parseResult;
    private array $hashTable;

    public function __construct(ParserHandler $parserHandler)
    {
        $this->parserHandler = $parserHandler;
    }

    public function handle(object $origin, object $page): array
    {
        // find origin regulars
        // unique   regulars hash table [hash => expression]
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

    private function parse(string $url, array $regulars)
    {
        $this->parseResult = $this->parserHandler->parse($url, $regulars);
        // parser ->  parse(page->url, regulars) = [[regular, matches]]
    }


}