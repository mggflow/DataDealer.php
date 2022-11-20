<?php

namespace MGGFLOW\DataDealer;

use MGGFLOW\DataDealer\Interfaces\PageHandler;

class HandlePage implements PageHandler
{
    public function handle(object $origin, object $page): array
    {
        //парсим
        $this->parse();

        //результаты разделяем на ссылки и целевые вхождения
        //ссылки валидируем, в первую очередь на предмет внутренних и внешних
        //затем ссылки сохраняем в бд
        //результаты, убираем дубликаты
        //затем результаты сохраняем в бд тоже
        //формируем объект результатов
    }

    public function parse()
    {

    }


}