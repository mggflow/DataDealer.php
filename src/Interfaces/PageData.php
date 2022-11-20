<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface PageData
{
    public function chooseOriginPage(int $originId): ?object;
}