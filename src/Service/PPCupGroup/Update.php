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
use App\Service\PPTournament;


final class Update  extends BaseService{
    public function __construct(
        protected PPCupGroupRepository $ppCupGroupRepository,
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroup\Find $ppCupGroupFindService,
        protected UserParticipation\Find $findUpService,
        protected UserParticipation\Create $createUpService,
        protected PPTournamentType\Find $findPPTournamentTypeService,
        protected PPTournament\VerifyAfterJoin $verify
    ) {}

    public function setFinished(int $id){
        $this->ppCupGroupRepository->setFinished($id);
        $ppCupGroup = $this->ppCupGroupRepository->getOne($id);

        $unfinishedCupGroups = $this->ppCupGroupRepository->getForCup($ppCupGroup['ppCup_id'],level: null, finished: 'IS NOT NULL');
        if(count($unfinishedCupGroups) === 0){
            $this->ppCupRepository->setFinished($ppCupGroup['ppCup_id']);
            return;
        }
        
        $this->handlePromotions($id);
    }

    private function handlePromotions(int $id){
        $ppCupGroup = $this->ppCupGroupRepository->getOne($id);
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppCupGroup['ppTournamentType_id']);
        $ups = $this->findUpService->getForTournament('ppCupGroup_id',$id);
        $promotions = $ppTournamentType['cup_format'][$ppCupGroup['level'] - 1]['promotions'];

        for ($i=0; $i < $promotions; $i++) { 
            if(!$nextGroup = $this->ppCupGroupFindService->getNextGroup($id, position: $i)){
                throw new \App\Exception\NotFound('next group not found', 500);
            };
            $this->createUpService->create($ups[$i]['user_id'], $ppTournamentType['id'], $nextGroup['ppCup_id'], $nextGroup['id']);
            $this->verify->afterJoined('ppCupGroup_id', $$nextGroup['id'], $ppTournamentType['id']);
        }
    }


}
