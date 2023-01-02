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


abstract class Base extends BaseService
{
    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected PPLeagueRepository $ppLeagueRepository,
        protected GuessRepository $guessRepository,
        protected PPRound\Find $ppRoundFindService,
    ){}


    protected function enrich(array &$up, int $userId){
        $up['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($up['ppTournamentType_id']);
        
        if($up['ppLeague_id']){
            $up['ppLeague'] = $this->ppLeagueRepository->getOne($up['ppLeague_id']);        
        }
        
        if($up['started'] && !$up['finished']){
            $column = $up['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';
            $userCurrentRound = $this->ppRoundFindService->getUserCurrentRound($column, $up[$column], $userId);
            
            $unlocked=0;
            $guesses = array_column($userCurrentRound, 'guess');
            foreach ($guesses as $guess) {
                if(!!$guess && !$guess['guessed_at'] && !$guess['verified_at'])$unlocked++;
            }
            $up['unlocked'] = $unlocked;
            
            $matches = array_column($userCurrentRound, 'match');
            if($matches){
                usort($matches, fn($a, $b) => $a['date_start'] <=> $b['date_start']);
                $up['nextMatch'] = $matches[0];
            }

        }       

        return;           
    }

}