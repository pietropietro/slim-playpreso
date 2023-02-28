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
    
    public function create(
        int $matchId, 
        ?int  $ppRoundId=null,
        ?string $tournamentColumn=null, 
        ?int $tournamentId=null
    ) : int {
        if(!$id = $this->ppRoundMatchRepository->create(
            $matchId, 
            $ppRoundId
        )){
            throw new \App\Exception\Mysql("could not create ppRoundMatch", 500);
        }

        if($tournamentColumn && $tournamentId){
            $this->guessCreateService->createForParticipants(
                $id, 
                $matchId, 
                $tournamentColumn, 
                $tournamentId
            );
        }
        return $id;
    }
    
}
