<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\RedisService;
use App\Repository\PPLeagueRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\UserRepository;
use App\Controller\BaseController;

final class Find  extends BaseController{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected UserRepository $userRepository,
    ) {
    }

    public function getAll($userId, $active){
        $ids = $this->userParticipationRepository->getUserPPLeagueIds($userId, $active);
        $ppLeagues = $this->ppLeagueRepository->getPPLeagues($ids);
        
        foreach($ppLeagues as $ppLKey => $ppLItem){
            $ppLeagues[$ppLKey]['ppLType'] = $this->ppLeagueTypeRepository->getOne($ppLItem['ppLeagueType_id']);
            //TODO ADD POINTS
            $ppLeagues[$ppLKey]['standings'] =  $this->calculateStandings($ppLItem['id']);
        }

        return $ppLeagues;
    }

    public function calculateStandings(int $ppLeagueId){
        $ids = $this->userParticipationRepository->getUserIds($ppLeagueId);
        $ppLeaguePositions = array();
        foreach ($ids as $id) {
            $userObject['username'] = $this->userRepository->getUsername($id);
            $userObject['id'] = $id;

            $position['user'] = $userObject;
            // $position['plPoints'] = userScore($presoLeagueID,$id);

            array_push($ppLeaguePositions, $position);
        }
    
        //TODO calculate position
        //sort ppLeaguePositions
        // $position['position'] = null;

        return $ppLeaguePositions;
    }

    public function userScore($ppLeagueId, $userId){

        //TODO

        $MBids = $db->subQuery();
        $MBids->where('presoLeague_id',$presoLeagueID);
        $MBids->get('matchBlocks',null,'id');

        $db->where('matchBlock_id',$MBids,'in');
        
        if($MBMids = $db->getValue('matchesMB','id',null)){
            $db->where('user_id',$userID);
            $db->where('MBM_id',$MBMids,'in');
            $db->where("preso_score != 222");

            if($presoScore = $db->getValue('guesses','sum(preso_score)',null)){
                if($presoScore[0]== null){
                    return 0;
                }
                return $presoScore[0];
            }
        }
        return 0;
    }
}
