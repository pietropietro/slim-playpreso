<?php

declare(strict_types=1);

namespace App\Service\PPCup;

use App\Repository\PPCupRepository;
use App\Repository\PPCupGroupRepository;
use App\Service\PPCupGroup;
use App\Service\PPRound;
use App\Service\PPTournamentType;
use App\Service\BaseService;

final class Update  extends BaseService{
    public function __construct(
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroupRepository $ppCupGroupRepository,
        protected PPCupGroup\Find $ppCupGroupfindService,
        protected PPTournamentType\Find $ppTournamentTypefindService,
        protected PPRound\Create $createPPRoundService,
    ) {}

    //CASCADE-START CUP GROUPS
    public function start(int $id, int $level){
        if($level===1)$this->ppCupRepository->setStarted($id);
        
        $levelGroups = $this->ppCupGroupfindService->getForCup($id, $level);
        if(!$levelGroups)return;

        $cupFormat = $this->ppTournamentTypefindService->getOne($levelGroups[0]['ppTournamentType_id'])['cup_format'];
        $matches_per_round = $cupFormat[$level - 1]->round_matches ?? null;

        foreach ($levelGroups as $key => $group) {
            $this->ppCupGroupRepository->setStarted($group['id']);
            $this->createPPRoundService->create('ppCupGroup_id', $group['id'], $group['ppTournamentType_id'], 1, $matches_per_round);
        }
    }

}
