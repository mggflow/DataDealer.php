<?php

namespace MGGFLOW\DataDealer;

use MGGFLOW\DataDealer\Interfaces\OriginData;
use MGGFLOW\DataDealer\Interfaces\PageData;
use MGGFLOW\DataDealer\Interfaces\PageHandler;
use MGGFLOW\DataDealer\Interfaces\StatData;
use MGGFLOW\ExceptionManager\ManageException;

class DealWithOrigin
{
    protected OriginData $originData;
    protected PageData $pageData;
    protected PageHandler $pageHandler;
    protected StatData $statData;

    protected ?object $origin;
    protected ?object $page;
    protected array $pageHandlingResult;
    protected ?int $statSupplementationResult;

    public function __construct(OriginData  $originData, PageData $pageData,
                                PageHandler $pageHandler, StatData $statData)
    {
        $this->originData = $originData;
        $this->pageData = $pageData;
        $this->pageHandler = $pageHandler;
        $this->statData = $statData;
    }

    public function deal(): array
    {
        $this->chooseOrigin();
        $this->checkOriginExistence();
        $this->chooseOriginPage();
        $this->checkPageExistence();
        $this->handlePage();
        $this->supplementStats();

        return $this->createSummary();
    }

    protected function chooseOrigin()
    {
        $this->origin = $this->originData->chooseOriginForDealing();
    }


    protected function checkOriginExistence()
    {
        if (empty($this->origin)) {
            throw ManageException::build()
                ->log()->info()->b()
                ->desc()->not('Origin')->found()->b()
                ->fill();
        }
    }

    protected function chooseOriginPage()
    {
        $this->page = $this->pageData->chooseOriginPage($this->origin->id);
    }

    protected function checkPageExistence()
    {
        if (empty($this->page)) {
            throw ManageException::build()
                ->log()->info()->b()
                ->desc()->not('Page')->found()
                ->context($this->origin->id, 'originId')->b()
                ->fill();
        }
    }

    protected function handlePage()
    {
        try{
            $this->pageHandlingResult = $this->pageHandler->handle($this->origin, $this->page);
        }catch (\Exception $e){
            $this->pageHandlingResult = [];
        }
    }

    protected function supplementStats()
    {
        $this->statSupplementationResult = $this->statData->supplementStat($this->origin->id, $this->pageHandlingResult);
    }

    protected function createSummary(): array
    {
        return [
            'origin' => $this->origin,
            'page' => $this->page,
            'pageHandlingResult' => $this->pageHandlingResult,
            'statSupplementationResult' => $this->statSupplementationResult
        ];
    }
}