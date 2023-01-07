<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

use App\Service\RedisService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\GuessRepository;

use App\Service\BaseService;
use App\Service\PPRound;
use App\Service\Match;


abstract class Base extends BaseService
{
    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected PPLeagueRepository $ppLeagueRepository,
        protected GuessRepository $guessRepository,
        protected PPRound\Find $ppRoundFindService,
        protected Match\Find $matchFindService
    ){}


    protected function enrich(array &$up, int $userId){
        $up['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($up['ppTournamentType_id']);
        
        if($up['ppLeague_id']){
            $ppLeague = $this->ppLeagueRepository->getOne($up['ppLeague_id']);      
            $up['rounds']= $up['ppTournamentType']['rounds'];
        }
        
        if($up['started'] && !$up['finished']){
            $column = $up['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';
            
            $up['currentRound'] = $this->ppRoundFindService->getCurrentRoundNumber($column, $up[$column]);
            $up['playedInCurrentRound'] = $this->ppRoundFindService->verifiedInLatestRound($column, $up[$column]);
            $up['user_count']= $this->userParticipationRepository->count($column, $up[$column]);

            // $userCurrentRound = $this->ppRoundFindService->getUserCurrentRound($column, $up[$column], $userId);

            $up['nextMatch'] = $this->matchFindService->getNextMatchInPPTournament($column, $up[$column]);
            // if($up['nextMatch']){
                //avoid heavy resp
                // unset($up['nextMatch']['league']['standings']);
            // }

        }       

        return;           
    }

}