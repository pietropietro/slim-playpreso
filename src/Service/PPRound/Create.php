<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Service\BaseService;
use App\Service\Match;
use App\Service\PPRoundMatch;
use App\Repository\PPRoundRepository;

final class Create extends BaseService{
    public function __construct(
        protected Match\Picker $matchPickerService,
        protected PPRoundRepository $ppRoundRepository,
        protected PPRoundMatch\Create $ppRoundMatchCreateService,
    ){}
    
    public function create(string $tournamentColumn, int $tournamentId, int $tournamentTypeId, int $newRound , ?int $matchesPerRound = null) : bool{
        if($this->ppRoundRepository->has($tournamentColumn, $tournamentId, $newRound))return false;

        $matchesPerRound = $matchesPerRound ?? 3;
        $picked = $this->matchPickerService->pick($tournamentTypeId, $matchesPerRound);
        if(!$picked) throw new \App\Exception\NotFound("no matches for new round", 500);
        if(!$newRoundId = $this->ppRoundRepository->create($tournamentColumn, $tournamentId, $newRound))return false;
        
        foreach ($picked as $key => $match) {
            $this->ppRoundMatchCreateService->create(
                $match['id'], 
                $newRoundId, 
                $tournamentColumn, 
                $tournamentId
            );
        }
        return true;
    }

}
