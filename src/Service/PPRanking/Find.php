<?php

namespace App\Service\PPRanking;

use App\Repository\PPRankingRepository;
use App\Repository\PPTournamentTypeRepository;
use App\Service\UserParticipation;
use App\Service\PPRanking;

class Find
{
    public function __construct(
        protected PPRankingRepository $ppRankingRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected UserParticipation\Find $userParticipationFindService,
        protected PPRanking\Calculate $calculatePPRankingService,
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
        $result = $this->ppRankingRepository->fetchRankingsByDate($date, $offset, $limit);


        foreach ($result['ppRankings'] as &$rankingItem) {
            

            $this->elaboratePPRankingPPLeagues($rankingItem);

            $this->elaboratePPRankingPPCups($rankingItem);

            //todo enrich ups for ppCups
            $rankingItem['from_guesses'] = $this->ppRankingRepository->fetchPointsFromGuesses(
                '-13weeks', $rankingItem['user_id']
            )[0];
        }

        return $result;
    }

    private function elaboratePPRankingPPLeagues(&$rankingItem){
        $ppLeaguesRecap = $this->ppRankingRepository->fetchPointsFromPPLeagues(
            '-13weeks', $rankingItem['user_id']
        );
        if(count($ppLeaguesRecap)==0)return;
        $rankingItem['from_ppLeagues'] =  $ppLeaguesRecap[0];
        if($rankingItem['from_ppLeagues']['tot_points'] > 0){
            $rankingItem['from_ppLeagues']['userParticipations'] = 
                $this->userParticipationFindService->get(
                    explode (",", $rankingItem['from_ppLeagues']['group_concat(ups.id)']),
                    true
            );
            foreach ($rankingItem['from_ppLeagues']['userParticipations'] as &$up) {
                $up['ppRanking_points'] = $this->calculatePPRankingService->getRankingPointsForPPLeaguePlacement(
                    $up['position'], $up['ppTournamentType']['level']);
            }
        }

    }

    private function elaboratePPRankingPPCups(&$rankingItem){
        $rankingItem['from_ppCups'] = [];
        $rankingItem['from_ppCups']['userParticipations'] = $this->ppRankingRepository->fetchPointsFromPPCups(
            '-13weeks', $rankingItem['user_id']
        );
        $totRankingPointsCups =  0;
        foreach ($rankingItem['from_ppCups']['userParticipations'] as &$ppCupItem) {
            $totRankingPointsCups += $ppCupItem['ppRanking_points'] ;

            $ppCupItem['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne(
                $ppCupItem['ppTournamentType_id']
            );
            $ppCupItem['ppTournamentType']['cup_format'] = json_decode($ppCupItem['ppTournamentType']['cup_format']);
            $ppCupItem['ppTournamentType']['levelFormat'] = $ppCupItem['ppTournamentType']['cup_format'][$ppCupItem['level'] - 1];
            unset($ppCupItem['ppTournamentType']['cup_format']);
            
            $ppCupItem['ppRanking_points'] = floor($this->calculatePPRankingService->getRankingPointsForPPCupPlacement(
                $ppCupItem['ppTournamentType']['levelFormat']->name, $ppCupItem['position'], $ppCupItem['ppTournamentType']['cost']
            ));
        }
        $rankingItem['from_ppCups']['tot_points'] = $totRankingPointsCups;
    }


    public function getForUser(int $userId){
        return $this->ppRankingRepository->fetchForUser($userId);
    }

    
}