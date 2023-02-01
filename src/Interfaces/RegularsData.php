<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface RegularsData
{
    public function findOriginRegulars(int $originId): ?array;
}