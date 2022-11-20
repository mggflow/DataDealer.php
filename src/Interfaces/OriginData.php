<?php

namespace MGGFLOW\DataDealer\Interfaces;

interface OriginData
{
    public function chooseOriginForDealing(): ?object;
}