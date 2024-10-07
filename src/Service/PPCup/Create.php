<?php

declare(strict_types=1);

namespace App\Service\PPCup;

use App\Repository\PPCupRepository;
use App\Repository\PPCupGroupRepository;
use App\Service\PPTournamentType;
use App\Service\BaseService;

final class Create extends BaseService{
    public function __construct(
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroupRepository $ppCupGroupRepository,
        protected PPTournamentType\Check $checkService,
        protected PPTournamentType\Find $findPPTTservice,
    ) {}

    public function create(int $ppTournamentType_id, ?string $slug){
        if(!$this->checkService->canCreateCup($ppTournamentType_id))return;
        if(!$format = $this->findPPTTservice->getOne($ppTournamentType_id)['cup_format'])return;
        
        $id = $this->ppCupRepository->create($ppTournamentType_id, $slug); 

        foreach ($format as $key => $cup_level) {
            $this->createLevelGroups(
                $id, 
                $ppTournamentType_id,
                $cup_level->level, 
                $cup_level->rounds, 
                $cup_level->group_tags, 
                $cup_level->group_participants);
        }
    }

    private function createLevelGroups(int $ppCupId, int $ppTournamentType_id, int $level, int $rounds, array $tags, ?int $participants = null){
        foreach ($tags as $key => $tag) {
            $this->ppCupGroupRepository->create($ppCupId, $ppTournamentType_id, $level, $rounds, $tag, $participants);
        }
    }

   
}
