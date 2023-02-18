<?php

namespace MGGFLOW\DataDealer;

use MGGFLOW\DataDealer\Interfaces\MatchData;
use MGGFLOW\DataDealer\Interfaces\MatchesOffsetData;

class FindFreshMatches
{
    private MatchesOffsetData $matchesOffsetData;
    private MatchData $matchData;
    private int $ownerId;
    private int $originId;
    protected int $regularId;

    private int $matchOffsetId;
    private int $lastMatchOffsetId;
    private ?array $matches;
    private ?object $matchesOffset;

    public function __construct(MatchesOffsetData $matchesOffsetData, MatchData $matchData,
                                int $ownerId, int $originId, int $regularId)
    {
        $this->matchesOffsetData = $matchesOffsetData;
        $this->matchData = $matchData;
        $this->ownerId = $ownerId;
        $this->originId = $originId;
        $this->regularId = $regularId;
    }

    public function find(int $count): ?array
    {
        $this->loadOffset();
        $this->verifyOffset();
        $this->findMatches($count);
        if (empty($this->matches)) return [];
        $this->getLastMatches();
        $this->saveLastMatchesOffset();

        return $this->matches;
    }

    private function loadOffset()
    {
        $this->matchesOffset = $this->matchesOffsetData->get($this->ownerId, $this->originId, $this->regularId);
    }

    private function verifyOffset()
    {
        if (empty($this->matchesOffset)) {
            $this->matchOffsetId = 0;
        }else{
            $this->matchOffsetId = $this->matchesOffset->offset_match_id;
        }

        $this->lastMatchOffsetId = 0;
    }

    private function findMatches($count)
    {
        $this->matches = $this->matchData->findAfter($this->matchOffsetId, $count);
    }

    private function getLastMatches()
    {
        foreach ($this->matches as $match) {
            if (is_array($match)) $match = (object)$match;
            if ($match->id > $this->lastMatchOffsetId)
                $this->lastMatchOffsetId = $match->id;
        }
    }

    private function saveLastMatchesOffset()
    {
        if ($this->matchOffsetId == $this->lastMatchOffsetId) return;

        if ($this->matchOffsetId == 0 and $this->lastMatchOffsetId > 0)
            $this->matchesOffsetData->create($this->ownerId, $this->originId, $this->regularId, $this->lastMatchOffsetId);

        if ($this->matchOffsetId != 0)
            $this->matchesOffsetData->update($this->matchesOffset->id, $this->lastMatchOffsetId);
    }
}