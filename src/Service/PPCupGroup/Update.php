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
        
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppCupGroup['ppTournamentType_id']);
        $promotionsPerGroup = $ppTournamentType['cup_format'][$ppCupGroup['level'] - 1]->promotions ?? null;

         //i.e. final
        if(!$promotionsPerGroup) return;
        
        //check promotion type of next level 
        $randomDraw = $ppTournamentType['cup_format'][$ppCupGroup['level']]->random_draw ?? null;
        
        //check if groups of same level are all over.
        $levelUnfinishedGroups = $this->ppCupGroupRepository->getForCup(
            $ppCupGroup['ppCup_id'],
            level: $ppCupGroup['level'], 
            finished: false
        );

        //schema promotion, no need to wait all other groups
        if(!$randomDraw){
            $this->handleSchemaPromotions($id, $promotionsPerGroup);
            //if the cup_format requires extra promotions check that.
            //like groups of euro cup where 6 groups need to provide 16 users.
            // so 3rd best positions are qualified for 4 spots
            //i.e extraPromotionsSlots =4  && extraPromotionPosition=3
            $extraPromotionsSlots = $ppTournamentType['cup_format'][$ppCupGroup['level'] - 1]->extra_promotions_slots ?? null;
            $extraPromotionPosition = $ppTournamentType['cup_format'][$ppCupGroup['level'] - 1]->extra_promotions_position ?? null;
            if( $extraPromotionsSlots && count($levelUnfinishedGroups) == 0){
                //TODO
                $this->handleExtraPromotions(
                    $ppCupGroup['ppCup_id'], 
                    $ppCupGroup['level'], 
                    $extraPromotionsSlots, 
                    $extraPromotionPosition
                );
            }
            return;
        }

       
        if(count($levelUnfinishedGroups) !== 0) return;

        $avoidPreviousLevelUsers = $ppTournamentType['cup_format'][$ppCupGroup['level']]->avoid_previous_level_users ?? null;

        $this->handleRandomDraw($ppCupGroup['ppCup_id'], $ppCupGroup['level'], $promotionsPerGroup, $avoidPreviousLevelUsers);
    }

    //TODO refactor all the promotion logics in separate service
    private function handleExtraPromotions(int $ppCupId, int $fromLevel, $extraSlots, $extraPosition){
        // 1. get best 3rd position for extraslots limit
        $upsInPosition = $this->findUpService->getForTournament(
            tournamentColumn: 'ppCup_id',
            tournamentId: $ppCupId,
            level: $fromLevel,
            enriched: false,
            position: $extraPosition,
            limit: $extraSlots,
            orderByPoints: true
        );

        $this->putUsersInGroups(
            $ppCupId, 
            $fromLevel + 1, 
            $upsInPosition, 
            true
        );
    }

    private function handleSchemaPromotions(int $ppCupGroupId, int $promotionsPerGroup){
        $ppCupGroup = $this->ppCupGroupRepository->getOne($ppCupGroupId);
        // $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppCupGroup['ppTournamentType_id']);
        $ups = $this->findUpService->getForTournament('ppCupGroup_id',$ppCupGroupId);
        
        for ($i=0; $i < $promotionsPerGroup; $i++) { 
            $nextGroup = $this->ppCupGroupFindService->getNextGroup($ppCupGroupId, positionIndex: $i);
            if(!$nextGroup){
                throw new \App\Exception\NotFound('next group not found', 500);
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

    private function handleRandomDraw(int $ppCupId, int $fromLevel, int $promotionsPerGroup, bool $avoidPreviousLevelUsers){
        $ups = $this->findUpService->getForTournament('ppCup_id', $ppCupId, $fromLevel, false);
        
        //random user from tier 1 against random user tier 2
        for ($i=1; $i <= $promotionsPerGroup; $i++) { 
            $filteredUps = array_filter($ups, function($item) use($i) {
                return $item['position'] == $i;
            });
            $this->putUsersInGroups($ppCupId, $fromLevel + 1, $filteredUps, $avoidPreviousLevelUsers);
        }
        
    }

    private function putUsersInGroups(
        int $ppCupId, 
        int $toLevel,
        array $ups, 
        bool $avoidPreviousLevelUsers
    ){
        shuffle($ups);

        foreach ($ups as $up) {

            $fromTag = null;
            if($avoidPreviousLevelUsers){
                $fromTag = $this->ppCupGroupRepository->getTag($up['ppCupGroup_id']);
            }

            $ppcg = $this->ppCupGroupFindService->getNotFull(
                $ppCupId, 
                $toLevel, 
                avoidFromTag: $fromTag
            );

            if(!$ppcg){
                return null;
            }

            $this->createUpService->create(
                $up['user_id'], 
                $up['ppTournamentType_id'],
                $ppCupId, 
                $ppcg['id'],
                (string) $up['ppCupGroup_id']
            );
        }
    }

}
