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
use App\Service\UserNotification;


final class Update  extends BaseService{
    public function __construct(
        protected PPCupGroupRepository $ppCupGroupRepository,
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroup\Find $ppCupGroupFindService,
        protected UserParticipation\Find $findUpService,
        protected UserParticipation\Create $createUpService,
        protected PPTournamentType\Find $findPPTournamentTypeService,
        protected UserNotification\Create $userNotificationCreateService
    ) {}

    public function setFinished(int $id){
        if(!$finished = $this->ppCupGroupRepository->setFinished($id)){
            throw new \App\Exception\NotFound('cant finish', 500);
        }
        $ppCupGroup = $this->ppCupGroupRepository->getOne($id);
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppCupGroup['ppTournamentType_id']);

        //check if cup is finished
        $unfinishedCupGroups = $this->ppCupGroupRepository->getForCup($ppCupGroup['ppCup_id'],level: null, finished: false);
        if(count($unfinishedCupGroups) === 0){
            $this->ppCupRepository->setFinished($ppCupGroup['ppCup_id'], $ppTournamentType);
            $this->sendNotifications($id, $ppTournamentType, true);
            return;
        }
        
        $this->handlePromotions($id);
    }

    private function sendNotifications(int $ppCupGroupid, array $ppTournamentType, bool $isFinal){
        $ups = $this->findUpService->getForTournament('ppCupGroup_id',$ppCupGroupId);
        foreach ($ups as $up) {
            if($isFinal){
                $title = $ppTournamentType['emoji'].' '.$ppTournamentType['name'].' is over';
                if($up['position']==1){
                    $body = "YOU ARE THE WINNER!";
                }else{
                    $body = "You lost the final";
                }
            }else{
                $title = $ppTournamentType['emoji'].' '.$ppTournamentType['name'].'. The group is over';

                if(in_array($up['position'], [1,21,31,41,51,61,71,81,91])){
                    $body = "You arrived ".$up['position']."st";
                }else if(in_array($up['position'], [2,22,32,42,52,62,72,82,92])){
                    $body = "You arrived ".$up['position']."nd";
                }else if(in_array($up['position'], [3,23,33,43,53,63,73,83,93])){
                    $body = "You arrived ".$up['position']."rd";
                }else{
                    $body = "You arrived ".$up['position']."th";
                }
            }
            
            $notificationText = array(
                'title' => $title,
                'body' => $body
            );
            
            $this->userNotificationCreateService->create(
                $up['user_id'],
                'ppcupgroup_finished',
                $up['id'], 
                $notificationText
            );
        }        
    }


    private function handlePromotions(int $ppCupGroupId, array $ppTournamentType){
        $ppCupGroup = $this->ppCupGroupRepository->getOne($ppCupGroupId);

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
        if($ppCupGroup['level'] == 1)$avoidPreviousLevelUsers = true;
        else{
            $avoidPreviousLevelUsers = $ppTournamentType['cup_format'][$ppCupGroup['level']]->avoid_previous_level_users ?? false;
        }
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
            null,
            null,
            true
        );
        
        $groupsNotFull = $this->ppCupGroupFindService->getNotFull(
            $ppCupId,
            $fromLevel +1,
            limitOne: false
        );
        $availableSlots = count($groupsNotFull) > 0;

        $position = 1;

        while ($availableSlots == true && $position<count($ups)) {
            $filteredUps = array_filter($ups, function($item) use($position) {
                return $item['position'] == $position;
            });

            $numToRemove = abs(count($groupsNotFull) - count($filteredUps));
            if($numToRemove > 0){
                $filteredUps = array_slice($filteredUps, 0, -$numToRemove);
            }

            $this->putUsersInAvailableGroups(
                $ppCupId, 
                $fromLevel + 1, 
                $filteredUps, 
                $avoidPreviousLevelUsers
            );

            $groupsNotFull = $this->ppCupGroupFindService->getNotFull(
                $ppCupId,
                $fromLevel +1,
                limitOne: false
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
                avoidFromTag: $avoidPreviousLevelUsers ? $fromTagClean : null,
                limitOne: true
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
