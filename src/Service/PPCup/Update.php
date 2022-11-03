<?php

declare(strict_types=1);

namespace App\Service\PPCup;

use App\Repository\PPCupRepository;
use App\Service\PPCupGroup;
use App\Service\BaseService;

final class Update  extends BaseService{
    public function __construct(
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroup\Find $ppCupGroupfindService,
        protected PPCupGroup\Update $ppCupGroupUpdateService,
        protected PPRound\Create $createPPRoundService,
    ) {}

    public function setFinished(int $id){
        $this->ppCupRepository->setFinished($id);
    }

    //CASCADE-START CUP GROUPS
    public function start(int $id){
        $this->ppCupRepository->setStarted($id);
        $ppCupGroups = $this->ppCupGroupfindService->getForCups($id);
        foreach ($ppCupGroups as $key => $group) {
            $this->ppCupGroupUpdateService->setStarted($group['id']);
            $this->createPPRoundService->create('ppCupGroup_id', $group['id'], $group['ppTournamentType_id'], 1);
        }
    }

    

}
