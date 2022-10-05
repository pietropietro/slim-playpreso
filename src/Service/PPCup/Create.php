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
            $this->createGroups($id, $format->level, $format->rounds, $format->groupTags);
        }
    }

    private function createGroups(int $ppCupId, int $level, int $rounds, array $tags){
        foreach ($tags as $key => $tag) {
            $this->ppCupGroupRepository->create($ppCupId, $level, $rounds, $tag);
        }
    }

   
}
