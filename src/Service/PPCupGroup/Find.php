<?php

declare(strict_types=1);

namespace App\Service\PPCupGroup;

use App\Service\RedisService;
use App\Service\UserParticipation;
use App\Service\PPRound;
use App\Service\BaseService;
use App\Repository\PPCupGroupRepository;


final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPCupGroupRepository $ppCupGroupRepository,
        protected UserParticipation\Find $userParticipationService,
        protected PPRound\Find $ppRoundFindService,
    ) {}

    public function getOne(int $id, ?bool $enriched=false, ?int $userId = null){
        $ppCupGroup = $this->ppCupGroupRepository->getOne($id);
        if(!$enriched)return $ppCupGroup;
        return $this->enrich($ppCupGroup, withRounds: true, userId: $userId);
    }

    public function getForCup(int $ppCupId, ?int $level = null, ?bool $finished = null){
        return $this->ppCupGroupRepository->getForCup($ppCupId, $level, $finished);
    }

    //positionIndex is 0 based
    public function getNextGroup(int $id, int $positionIndex){
        $ppCupGroup = $this->getOne($id);

        $nextGroups = $this->getForCup($ppCupGroup['ppCup_id'], level: $ppCupGroup['level'] + 1);
        if(!$nextGroups)return;

        if($ppCupGroup['level']==1){
            foreach ($nextGroups as $group) {
                if($group['tag'][$positionIndex]==$ppCupGroup['tag'])return $group;
            }
        }
        else{
            $previoustaglength = strlen($ppCupGroup['tag']);
            foreach ($nextGroups as $group) {
                if(substr($group['tag'],0,$previoustaglength) === $ppCupGroup['tag'])return $group;
                if(substr($group['tag'], $previoustaglength, $previoustaglength) === $ppCupGroup['tag'])return $group;
            }
        }
    }

    public function getCurrentCupLevel(int $ppCupId) : int{
        return $this->ppCupGroupRepository->getCurrentCupLevel($ppCupId);
    }
    
    public function getLevels(int $ppCupId) : array{
        $levels = [];
        $groups = $this->getForCup($ppCupId);

        foreach($groups as $group){
            $currentLevel = $group['level'];
            if(!in_array($currentLevel, array_keys($levels))){
                $levels[$currentLevel] = [];
            }
            $group = $this->enrich($group);
            array_push($levels[$currentLevel], $group);
        }
        return $levels;
    }

    public function enrich(array $group, ?bool $withRounds=false, ?int $userId=null){
        $group['userParticipations'] = $this->userParticipationService->getForTournament('ppCupGroup_id', $group['id']);
        if($group['started_at'] && !$group['finished_at'] ){
            $group['isLive'] = $this->ppRoundFindService->hasLiveMatch('ppCupGroup_id', $group['id']);
            $group['currentRound'] = $this->ppRoundFindService->getCurrentRoundNumber('ppCupGroup_id', $group['id']);
            $group['playedInCurrentRound'] = $this->ppRoundFindService->verifiedInLatestRound('ppCupGroup_id', $group['id']);    
        }
        if($withRounds){
            $group['ppRounds'] = $this->ppRoundFindService->getForTournament('ppCupGroup_id', $group['id'], $userId);
            // $group['userCurrentRound'] = $userId ? 
            //     $this->ppRoundFindService->getUserCurrentRound('ppCupGroup_id', $group['id'], $userId) 
            //     : null;
        }
    
        return $group;
    }
    
    public function getNotFull(int $ppCupId, int $level) : ?array{
        $ppCupGroup = $this->ppCupGroupRepository->getNotFull($ppCupId, $level);
        return $ppCupGroup ? $ppCupGroup[0] : null;
    }
}
