<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

use App\Service\RedisService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\PPRoundRepository;
use App\Repository\GuessRepository;

use App\Service\BaseService;


abstract class Base extends BaseService
{
    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPRoundRepository $ppRoundRepository,
        protected GuessRepository $guessRepository
    ){}


    public function addPPLeagueData(&$up){
        $up['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($up['ppTournamentType_id']);
        $up['ppLeague'] = $this->ppLeagueRepository->getOne($up['ppLeague_id']);        
        
        //TODO fix this shit
        if($up['ppLeague']['started_at'] && !$up['ppLeague']['finished_at']){
            $up['locked'] = !$this->guessRepository->hasUnlockedGuesses($up['user_id'], 'ppLeague_id', $up['ppLeague_id']); 
        }       

        return;           
    }

}