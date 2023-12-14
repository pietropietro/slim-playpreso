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
        
        //check promotion type
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppCupGroup['ppTournamentType_id']);
        $tierDraw = $ppTournamentType['cup_format'][$ppCupGroup['level']]->tier_one_tier_two_draw ?? null;

        //schema promotion
        if(!$tierDraw){
            $this->handleSchemaPromotions($id);
            return;
        }

        //random tier 1 vs tier 2 promotions
        $levelUnfinishedGroups = $this->ppCupGroupRepository->getForCup(
            $ppCupGroup['ppCup_id'],
            level: $ppCupGroup['level'], 
            finished: false
        );

        if(count($levelUnfinishedGroups) === 0){
            $this->handleRandomTieredPromotions($ppCupGroup['ppCup_id'], $ppCupGroup['level']);
            //TODO EMAIL NOTIFY
        }
        
    }

    private function handleSchemaPromotions(int $id){
        $ppCupGroup = $this->ppCupGroupRepository->getOne($id);
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppCupGroup['ppTournamentType_id']);
        $ups = $this->findUpService->getForTournament('ppCupGroup_id',$id);

        $promotions = $ppTournamentType['cup_format'][$ppCupGroup['level'] - 1]->promotions ?? null;
        //i.e. final
        if(!$promotions) return;

        for ($i=0; $i < $promotions; $i++) { 
            if(!$nextGroup = $this->ppCupGroupFindService->getNextGroup($id, positionIndex: $i)){
                throw new \App\Exception\NotFound('next group not found', 500);
            };

            $this->createUpService->create(
                $ups[$i]['user_id'], 
                $ppTournamentType['id'],
                $nextGroup['ppCup_id'], 
                $nextGroup['id'], 
                fromTag: $ppCupGroup['tag']
            );
            
        }
    }

    //random user from tier 1 against random user tier 2
    private function handleRandomTieredPromotions(int $ppCupId, int $level){
        $ups = $this->findUpService->getForTournament('ppCup_id', $ppCupId, $level, false);
        
        $upsOne = array_filter($ups, function($item) {
            return $item['position'] == 1;
        });
        $upsTwo = array_filter($ups, function($item) {
            return $item['position'] == 2;
        });

        $this->joinAvoidingOldOpponent($ppCupId, $level, $upsOne);
        $this->joinAvoidingOldOpponent($ppCupId, $level, $upsTwo);
    }

    private function joinAvoidingOldOpponent(int $ppCupId, int $level, array $ups){
        foreach ($ups as $up) {
            $ppcg = $this->ppCupGroupFindService->getNotFull(
                $ppCupId, 
                $level + 1, 
                avoidFromTag: (string) $up['ppCupGroup_id']
            );

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
