<?php

declare(strict_types=1);

namespace App\Service\PPCupGroup;

use App\Repository\PPCupGroupRepository;
use App\Repository\PPCupRepository;
use App\Service\BaseService;
use App\Service\PPCup;
use App\Service\PPCupGroup;
use App\Service\UserParticipation;
use App\Service\PPTournamentType;


final class Update  extends BaseService{
    public function __construct(
        protected PPCupGroupRepository $ppCupGroupRepository,
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroup\Find $ppCupGroupFindService,
        protected UserParticipation\Find $findUpService,
        protected UserParticipation\Create $createUpService,
        protected PPTournamentType\Find $findPPTournamentTypeService,
    ) {}

    public function setFinished(int $id){
        if(!$finished = $this->ppCupGroupRepository->setFinished($id)){
            throw new \App\Exception\NotFound('cant finish', 500);
        }
        $ppCupGroup = $this->ppCupGroupRepository->getOne($id);

        //check if cup is finished
        $unfinishedCupGroups = $this->ppCupGroupRepository->getForCup($ppCupGroup['ppCup_id'],level: null, finished: false);
        if(count($unfinishedCupGroups) === 0){
            $this->ppCupRepository->setFinished($ppCupGroup['ppCup_id']);
            return;
        }
        
        $this->handlePromotions($id);
    }

    private function handlePromotions(int $ppCupGroupId){
        $ppCupGroup = $this->ppCupGroupRepository->getOne($ppCupGroupId);

        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppCupGroup['ppTournamentType_id']);
        $promotionsPerGroup = $ppTournamentType['cup_format'][$ppCupGroup['level'] - 1]->promotions ?? null;
        $randomDraw = $ppTournamentType['cup_format'][$ppCupGroup['level']]->random_draw ?? null;
        
        //check if groups of same level are all over.
        $levelUnfinishedGroups = $this->ppCupGroupRepository->getForCup(
            $ppCupGroup['ppCup_id'],
            level: $ppCupGroup['level'], 
            finished: false
        );

        if(!$randomDraw){
            //schema promotion, no need to wait all other groups
            $this->handleSchemaPromotions($ppCupGroupId, $promotionsPerGroup);
        }

        if(count($levelUnfinishedGroups) != 0 ) return;

        //after schema promotion was done for all groups, try random promotion if there are available slots
        $avoidPreviousLevelUsers = $ppTournamentType['cup_format'][$ppCupGroup['level']]->avoid_previous_level_users ?? false;
        $this->handleRandomPromotions($ppCupGroup['ppCup_id'], $ppCupGroup['level'], $avoidPreviousLevelUsers);
    }

    private function handleSchemaPromotions(int $ppCupGroupId, int $promotionsPerGroup){
        $ppCupGroup = $this->ppCupGroupRepository->getOne($ppCupGroupId);
        $ups = $this->findUpService->getForTournament('ppCupGroup_id',$ppCupGroupId);
        
        for ($i=0; $i < $promotionsPerGroup; $i++) { 
            $nextGroup = $this->ppCupGroupFindService->getNextGroup($ppCupGroupId, positionIndex: $i);
            if(!$nextGroup){
                return;
            };

            $this->createUpService->create(
                $ups[$i]['user_id'], 
                $ppCupGroup['ppTournamentType_id'],
                $nextGroup['ppCup_id'], 
                $nextGroup['id'], 
                fromTag: $ppCupGroup['tag']
            );
        }
    }

    private function handleRandomPromotions(int $ppCupId, int $fromLevel, bool $avoidPreviousLevelUsers){
        //ups order by points
        $ups = $this->findUpService->getForTournament(
            'ppCup_id', 
            $ppCupId, 
            $fromLevel, 
            false,
            null,
            null,
            true
        );
        
        $availableSlots = count($this->ppCupGroupFindService->getNotFull(
            $ppCupId,
            $fromLevel +1
        )) > 0;

        $position = 1;

        while ($availableSlots == true && $position<count($ups)) {
            $filteredUps = array_filter($ups, function($item) use($position) {
                return $item['position'] == $position;
            });
            $this->putUsersInAvailableGroups(
                $ppCupId, 
                $fromLevel + 1, 
                $filteredUps, 
                $avoidPreviousLevelUsers
            );
            $groupsNotFull = $this->ppCupGroupFindService->getNotFull(
                $ppCupId,
                $fromLevel +1
            );
            $availableSlots = $groupsNotFull && count($groupsNotFull) > 0 ? true : false;
            $position ++;
        }
    }

    private function putUsersInAvailableGroups(
        int $ppCupId, 
        int $toLevel,
        array $ups, 
        bool $avoidPreviousLevelUsers
    ){
        shuffle($ups);

        foreach ($ups as $up) {

            $fromTag = $this->ppCupGroupRepository->getTag($up['ppCupGroup_id']);
            //some ppTournaments like euro cup might have + or - inside tag string
            //we do not need to consider it here
            $fromTagClean = str_replace(['+', '-'], '', $fromTag);

            $ppcg = $this->ppCupGroupFindService->getNotFull(
                $ppCupId, 
                $toLevel, 
                avoidFromTag: $avoidPreviousLevelUsers ? $fromTagClean : null
            );

            if(!$ppcg){
                return null;
            }

            $this->createUpService->create(
                $up['user_id'], 
                $up['ppTournamentType_id'],
                $ppCupId, 
                $ppcg['id'],
                $fromTagClean
            );
        }
    }

}
