<?php

namespace MGGFLOW\DataDealer;

class HashString
{
    public static function hash(string $str): string
    {
        return md5($str);
    }
}