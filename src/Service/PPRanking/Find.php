<?php

namespace App\Service\PPRanking;

use App\Repository\PPRankingRepository;

class Find
{
    public function __construct(
        protected PPRankingRepository $ppRankingRepository,
    ){}

    /**
     * Retrieves rankings for a specific date.
     *
     * @param string $date
     * @return array
     */
    public function getRankingsForDate(
        ?string $date=null, 
        ?int $page = 1, 
        ?int $limit = 20, 
    ): array
    {
        $offset = ($page - 1) * $limit;
        $ppRankings = $this->ppRankingRepository->fetchRankingsByDate($date, $offset, $limit);
        return $ppRankings;
    }


    public function getForUser(int $userId){
        return $this->ppRankingRepository->fetchForUser($userId);
    }

    
}