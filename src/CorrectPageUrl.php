<?php

namespace MGGFLOW\DataDealer;

class CorrectPageUrl
{
    public function correct() {

    }

    protected function removeURLFragment($pstr_urlAddress = '') {
        $larr_urlAddress = parse_url ( $pstr_urlAddress );
        return $larr_urlAddress['scheme'].'://'.(isset($larr_urlAddress['user']) ? $larr_urlAddress['user'].':'.''.$larr_urlAddress['pass'].'@' : '').$larr_urlAddress['host'].(isset($larr_urlAddress['port']) ? ':'.$larr_urlAddress['port'] : '').$larr_urlAddress['path'].(isset($larr_urlAddress['query']) ? '?'.$larr_urlAddress['query'] : '');
    }
}