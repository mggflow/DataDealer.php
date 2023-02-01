<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface PageData
{
    public function chooseOriginPage(int $originId): ?object;
    public function addAny(array $pages): ?int;
}