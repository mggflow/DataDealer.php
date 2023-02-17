<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface MatchesOffsetData
{
    public function get(int $ownerId, int $originId, int $regularId): ?object;

    public function create(int $ownerId, int $originId, int $regularId, int $offsetId): ?int;

    public function update(int $id, int $offsetId): ?int;
}