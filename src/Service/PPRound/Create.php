<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Repository\PPRoundRepository;
use App\Repository\LeagueRepository;
use App\Repository\PPRoundMatchRepository;
use App\Repository\MatchRepository;
use App\Repository\GuessRepository;


final class Verify  extends BaseService{
    public function __construct(
        protected LeagueRepository $leagueRepository,
    ){}
    
    public function create(string $tournamentColumn, int $tournamentId, int $tournamentTypeId, int $newRound){
        //get league ids 
        //leagueservice->getForPPLeagueType(tournamentTypeId)
        //$picked = matchpicker->pick();
        // if($picked.length !== 3) return
        //$newRoundId = pproundrepo->create($tournamentColumn, $tournamentId, $newRound);
        //foreach picked
        //$newRoundMatchId =RoundId pproundmatchrepo->create($newRoundId, $match['id]);
        //guessservicecreate->createForPPRoundMatch($match['id'], $newRoundMatchId);

    }

}
