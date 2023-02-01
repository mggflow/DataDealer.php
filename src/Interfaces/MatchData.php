<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface MatchData
{
    public function addAny(array $matches): ?int;

    public function findAfter(int $id, int $count): ?array;
}