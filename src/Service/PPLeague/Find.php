<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\RedisService;
use App\Repository\PPLeagueRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\UserRepository;
use App\Repository\GuessRepository;
use App\Controller\BaseController;

final class Find  extends BaseController{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected UserRepository $userRepository,
        protected GuessRepository $guessRepository,
    ) {
    }

    public function getAll($userId, $active){
        $ids = $this->userParticipationRepository->getUserPPLeagueIds($userId, $active);
        $ppLeagues = $this->ppLeagueRepository->getPPLeagues($ids);
        
        foreach($ppLeagues as $ppLKey => $ppLItem){
            $ppLeagues[$ppLKey]['ppLType'] = $this->ppLeagueTypeRepository->getOne($ppLItem['ppLeagueType_id']);
            //TODO ADD POINTS
            $ppLeagues[$ppLKey]['ppStandings'] =  $this->calculateStandings($ppLItem['id']);
        }

        return $ppLeagues;
    }

    public function calculateStandings(int $ppLeagueId){
        $ids = $this->userParticipationRepository->getUserIds($ppLeagueId);
        $ppLeaguePositions = array();
        foreach ($ids as $userId) {
            $userObject['username'] = $this->userRepository->getUsername($userId);
            $userObject['id'] = $userId;

            $position['user'] = $userObject;
            $position['score'] = $this->guessRepository->userScore($userId,'ppLeague_id',$ppLeagueId);

            array_push($ppLeaguePositions, $position);
        }
    
        //TODO calculate position
        //sort ppLeaguePositions
        // $position['position'] = null;

        return $ppLeaguePositions;
    }

}
