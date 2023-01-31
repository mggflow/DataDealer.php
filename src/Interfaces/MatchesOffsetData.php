<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface MatchesOffsetData
{
    public function get(int $ownerId, int $originId);
    public function create(int $ownerId, int $originId, int $offsetId);
    public function update(int $id, int $offsetId);
}