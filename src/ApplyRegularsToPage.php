<?php

namespace MGGFLOW\DataDealer;

class ApplyRegularsToPage
{
    protected array $uniqueRegulars;
    protected array $parsingResult;

    protected string $regularHash;
    protected array $regularBasic;

    protected array $matches;
    protected array $stats;


    public function apply(array $uniqueRegulars, array $parsingResult): array
    {
        $this->uniqueRegulars = $uniqueRegulars;
        $this->parsingResult = $parsingResult;
        $this->stats = [];
        $this->matches = [];

        foreach ($this->uniqueRegulars as $this->regularHash=>$this->regularBasic)
        {
            $this->initStat();
            if ($this->parsingResult['response_code']!=200){
                $this->addDealingFail();
            }

            $this->matches[$this->regularHash] = [];
            preg_match_all($this->regularBasic['expression'], $this->parsingResult['html'], $this->matches[$this->regularHash]);
            $this->matches[$this->regularHash] = array_unique($this->matches[$this->regularHash][0]);

            $this->setMatchesCount();
        }

        return $this->getResult();
    }

    protected function initStat() {
        $this->stats[$this->regularBasic['id']] = [
            'matches_found' => 0,
            'dealing_times' => 1,
            'dealing_fails' => 0,
        ];
    }


    protected function addDealingFail() {
        $this->stats[$this->regularBasic['id']]['dealing_fails'] += 1;
    }

    protected function setMatchesCount() {
        $this->stats[$this->regularBasic['id']]['found'] = count($this->matches[$this->regularHash]);
    }

    protected function getResult(): array
    {
        return [
            'stats' => $this->stats,
            'matches' => $this->matches
        ];
    }
}