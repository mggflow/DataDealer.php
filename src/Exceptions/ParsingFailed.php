<?php

namespace MGGFLOW\DataDealer\Exceptions;

class ParsingFailed extends \Exception
{
    protected $message = 'Failed to parse HTML by Url.';
}