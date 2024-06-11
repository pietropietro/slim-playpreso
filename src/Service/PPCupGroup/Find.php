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
    public function getNextGroup(int $fromPPCupGroup_id, int $positionIndex, ){
        $fromPPCupGroup = $this->getOne($fromPPCupGroup_id);

        $nextGroups = $this->getForCup($fromPPCupGroup['ppCup_id'], level: $fromPPCupGroup['level'] + 1);
        if(!$nextGroups)return;

        $fromTag = $fromPPCupGroup['tag'];

        // Level 1 handling with special character '+' consideration
        //DE means 1st post of group D against 2nd of group E but
        //+DE means that 2nd pos of group D against 2nd of group E
        if ($fromPPCupGroup['level'] == 1) {
            foreach ($nextGroups as $group) {
                $groupTag = $group['tag'];
                $cleanTag = str_replace('+', '', $groupTag);
    
                // Check if the position directly matches the tag
                if ($groupTag[$positionIndex] == $fromTag) {
                    return $group;
                }

                // Check if there's a '+' indicating a position shift
                $hasPlus = strpos($groupTag, '+') !== false;
                $containsFromTag = strpos($cleanTag, $fromTag) !== false;

                if ($hasPlus && $containsFromTag && $positionIndex === 1) {
                    return $group;
                }
            }
        } else {
            // Higher level handling: clean the tags before comparison
            $previoustaglength = strlen($fromTag);
            foreach ($nextGroups as $group) {
                $cleanTag = str_replace('+', '', $group['tag']);
                if(substr($cleanTag, 0, $previoustaglength) === $ppCupGroup['tag'])return $group;
                if(substr($cleanTag, $previoustaglength, $previoustaglength) === $ppCupGroup['tag'])return $group;
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

    public function getPaused(){
        return $this->ppCupGroupRepository->getPaused();
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
    
    public function getNotFull(int $ppCupId, int $level, ?string $avoidFromTag = null) : ?array{
        $ppCupGroup = $this->ppCupGroupRepository->getNotFull($ppCupId, $level, $avoidFromTag);
        return $ppCupGroup ? $ppCupGroup[0] : null;
    }
}
