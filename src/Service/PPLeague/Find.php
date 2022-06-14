<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\RedisService;
use App\Repository\PPLeagueRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\PPRoundRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\UserRepository;
use App\Repository\GuessRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected PPRoundRepository $ppRoundRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected UserRepository $userRepository,
        protected GuessRepository $guessRepository,
    ) {
    }

    public function getOne($ppLeagueId){
        return $this->ppLeagueRepository->getOne($ppLeagueId);
    }

    function getJoinable(int $typeId, int $userId){
        if($ppLT = $this->ppLeagueRepository->getJoinable($typeId)){
            return $ppLT;
        }
        $id = $this->ppLeagueRepository->create($typeId);
        return $this->ppLeagueRepository->getOne($id);
    }

    //TODO MOVE
    //FOR THE SAKE OF IT
    private function updateAllStandings(){
        $ids = $this->ppLeagueRepository->startedIds();
        foreach($ids as $id){
            $this->calculateStandings($id);
        }
    }
    //TODO MOVE
    private function countAllPPLRounds(){
        $ids = $this->ppLeagueRepository->startedIds();
        foreach($ids as $id){
            $round_count = $this->ppRoundRepository->count('ppLeague_id',$id);
            $this->ppLeagueRepository->updateValue($id, 'round_count', $round_count);
        }
    }


    //TODO MOVE IN OTHER SERVICE, update i.e,
    public function calculateStandings(int $ppLeagueId){
        $ups = $this->userParticipationRepository->getLeagueParticipations($ppLeagueId);
        foreach ($ups as $upKey => $upItem) {
            $ups[$upKey]['score'] = $this->guessRepository->userScore($upItem['user_id'],'ppLeague_id',$ppLeagueId);
        }
       
        ////TODO also sort by number of PRESO!, less MISSED, 1X2, UO, GG
        usort($ups, fn($a, $b) => $a['score'] < $b['score'] ? 1 : 0);

        foreach($ups as $index => $upItem){
            $this->userParticipationRepository->updateScore($upItem['id'], $upItem['score']);
            $this->userParticipationRepository->updatePosition($upItem['id'], $index + 1);
        }
        return;
    }

    

}
