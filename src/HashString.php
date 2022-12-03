<?php

namespace MGGFLOW\DataDealer;

class HashString
{
    public static function hash(string $str) {
        return md5($str);
    }
}