<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface ParserHandler
{
    public function parse(string $url, array $regulars);
}