<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Repository\PPLeagueRepository;
use App\Service\UserParticipation;
use App\Service\PPTournamentType;
use App\Service\Points;
use App\Service\BaseService;

final class Update  extends BaseService{
    public function __construct(
        protected PPLeagueRepository $ppLeagueRepository,
        protected UserParticipation\Find $userParticipationFindService,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
        protected PPTournamentType\Join $ppTournamentTypeJoinService,
        protected Points\Update $pointsUpdateService,
    ) {}

    public function setStarted(int $id){
        $this->ppLeagueRepository->setStarted($id);
    }
    public function afterFinished(int $id){
        $ppLeague = $this->ppLeagueRepository->getOne($id);
        $this->ppLeagueRepository->setFinished($id);
    
        $ups = $this->userParticipationFindService->getForTournament('ppLeague_id', $id);
    
        // $this->rewardPoints($ups, $ppLeague['ppTournamentType_id']);
        $this->promote($ups, $ppLeague['ppTournamentType_id']);
    }


    private function promote(array $ups, int $fromPPTTId){
        $ppTournamentType = $this->ppTournamentTypeFindService->getOne($fromPPTTId);
        if(!$ppTournamentType['next'] || !$ppTournamentType['promote']) return;
       
        for($i = 0; $i<$ppTournamentType['promote'] ; $i++){
            $this->ppTournamentTypeJoinService->joinAvailable(
                $ups[$i]['user_id'], 
                $ppTournamentType['next']['id'],
                pay: false
            );
        }
        
    }

    // private function rewardPoints(array $ups, $ppTournamentType_id){
        // $pointPrizes = $this->calculatePointRewards($ppTournamentType_id);
        // foreach ($pointPrizes as $index => $prize) {
        //     $this->pointsUpdateService->plus($ups[$index]['user_id'], $prize);
        // }
    // }

    private function calculatePointRewards($id) {
        $ppTournamentType = $this->ppTournamentTypeFindService->getOne($id);

        $jackpot = $ppTournamentType['cost'] * ($ppTournamentType['participants'] / 2);
        $pointsPositions = floor($ppTournamentType['participants']/3);
        
        $pointRewards = [];
    
        while ($pointsPositions > 0) {
          $part = floor($jackpot / 2);
          $jackpot -= $part;
          array_push($pointRewards, $part);
          $pointsPositions--;
        }

        return $pointRewards;
    }

}
