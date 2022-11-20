<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface PageHandler
{
    public function handle(object $origin, object $page): array;
}