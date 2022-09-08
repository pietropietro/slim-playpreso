<?php

declare(strict_types=1);

namespace App\Service\PPRoundMatch;

use App\Service\BaseService;
use App\Repository\PPRoundMatchRepository;
use App\Service\Guess;

final class Create  extends BaseService{
    public function __construct(
        protected PPRoundMatchRepository $ppRoundMatchRepository,
        protected Guess\Create $guessCreateService,
    ){}
    
    public function create(int $ppRoundId, int $matchId, string $tournamentColumn, int $tournamentId) : int {
        if(!$id = $this->ppRoundMatchRepository->create($ppRoundId, $matchId)){
            throw new \App\Exception\Mysql("could not create ppRoundMatch", 500);
        }
        $this->guessCreateService->createForParticipants($id, $matchId, $tournamentColumn, $tournamentId);
        return $id;
    }
    
}
