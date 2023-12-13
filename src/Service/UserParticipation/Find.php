<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;
use App\Service\BaseService;
use App\Service\RedisService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\PPLeagueRepository;
use App\Service\PPRound;
use App\Service\Match;
use App\Service\Trophy;

final class Find  extends BaseService {

    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPRound\Find $ppRoundFindService,
        protected Match\Find $matchFindService,
        protected Trophy\Find $trophiesFindService,
    ){}


    public function getForTournament(string $tournamentColumn, int $tournamentId,?int $level = null,?bool $enriched = true) :array{
        $ups = $this->userParticipationRepository->getForTournament($tournamentColumn, $tournamentId); 
        
        if(!$enriched) return $ups;

        foreach ($ups as &$up) {
            $up['user']['id'] = $up['user_id'];
            $up['user']['username'] = $up['username'];
            $up['user']['trophies'] = $this->trophiesFindService->getTrophies($up['user']['id']);
        }
        return $ups;
    }

    public function countInTournament(string $tournamentColumn, int $tournamentId){
        return $this->userParticipationRepository->count($tournamentColumn, $tournamentId);
    }

    public function getForUser(
        int $userId, 
        ?string $playMode, 
        ?bool $started = null, 
        ?bool $finished = null, 
        ?string $updatedAfter = null
    ){
        $ups = $this->userParticipationRepository->getForUser(
            $userId, 
            $playMode ? $playMode.'_id' : null,
            $started, 
            $finished, 
            null,
            $updatedAfter
        );        
        foreach($ups as &$up){
            $this->enrich($up, $userId);
        }
        return $ups;
    }

    public function isUserInTournament(int $userId, string $tournamentColumn, int $tournamentId){
        return $this->userParticipationRepository->isUserInTournament($userId, $tournamentColumn, $tournamentId);
    }

    public function isUserInTournamentType(int $userId, int $ppTournamentType_id){
        return $this->userParticipationRepository->isUserInTournamentType($userId, $ppTournamentType_id);
    }

    protected function enrich(array &$up, int $userId){
        $up['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($up['ppTournamentType_id']);
        
        if($up['ppLeague_id']){
            $ppLeague = $this->ppLeagueRepository->getOne($up['ppLeague_id']);      
            $up['rounds']= $up['ppTournamentType']['rounds'];
        }
        
        
        $column = $up['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';

        if(!$up['started']){
            $up['user_count']= $this->userParticipationRepository->count($column, $up[$column]);
        }

        if($up['started']){
            $up['currentRound'] = $this->ppRoundFindService->getCurrentRoundNumber($column, $up[$column]);
            $up['playedInCurrentRound'] = $this->ppRoundFindService->verifiedInLatestRound($column, $up[$column]);

            // $userCurrentRound = $this->ppRoundFindService->getUserCurrentRound($column, $up[$column], $userId);
            if(!$up['finished'])$up['nextMatch'] = $this->matchFindService->getNextMatchInPPTournament($column, $up[$column]);
            
            //set paused
            if(!$up['finished'] && !$up['nextMatch']){
                $up['paused'] = true;
            }
        }       
        return;        
    }

    //returns started ups, dividing them in active and paused, i.e. waiting for matches
    public function getActiveAndPausedPPLeaguesForUser($userId){
        $ups = $this->getForUser(
            $userId, 'ppLeague', true, false
        );

        $active = [];
        $paused = [];

        foreach ($ups as $up) {
            if(isset($up['paused'])){
                array_push($paused, $up);
                continue;
            }
            array_push($active, $up);
        }
        return ['active' => $active, 'paused' => $paused];
    }
}
