<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface StatData
{
    public function supplementStat(int $originId, array $pageHandlingResult): ?int;
}