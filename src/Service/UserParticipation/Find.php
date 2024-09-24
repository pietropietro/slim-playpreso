<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;
use App\Service\BaseService;
use App\Service\RedisService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\PPCupGroupRepository;
use App\Repository\PPLeagueRepository;
use App\Service\PPRound;
use App\Service\Match;
use App\Service\Trophy;
use App\Service\PPTournamentType;

final class Find  extends BaseService {

    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPCupGroupRepository $ppCupGroupRepository,
        protected PPRound\Find $ppRoundFindService,
        protected Match\Find $matchFindService,
    ){}

    public function get(?array $ids=[], ?bool $withPPTT = false){
        $ups = $this->userParticipationRepository->get($ids);
        if($withPPTT){
            foreach ($ups as &$up) {
                $up['ppTournamentType'] = $this->ppTournamentTypeFindService->getOne($up['ppTournamentType_id'], false);
            }
        }
        return $ups;
    }


    public function getOne(int $id, ?bool $enrich = true){
        $up = $this->userParticipationRepository->getOne($id);
        if($enrich)$this->enrich($up);
        return $up;
    }


    public function getForTournament(
        string $tournamentColumn,
        int $tournamentId,
        ?int $level = null,
        ?int $position = null,
        ?int $limit=null,
        ?bool $orderByPoints=null
    ) :array {
        return $this->userParticipationRepository->getForTournament(
            $tournamentColumn,
            $tournamentId,
            $level,
            $position,
            $limit,
            $orderByPoints
        ); 
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
            $this->enrich($up, false);
        }
        return $ups;
    }

    public function isUserInTournament(int $userId, string $tournamentColumn, int $tournamentId){
        return $this->userParticipationRepository->isUserInTournament($userId, $tournamentColumn, $tournamentId);
    }

    public function isUserInTournamentType(int $userId, int $ppTournamentType_id){
        return $this->userParticipationRepository->isUserInTournamentType($userId, $ppTournamentType_id);
    }

    protected function enrich(&$up, ?bool $ppttEnriched = true){
        if(!$up) return;
        // $up['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($up['ppTournamentType_id']);
        $up['ppTournamentType'] = $this->ppTournamentTypeFindService->getOne($up['ppTournamentType_id'], $ppttEnriched);
        
        if($up['ppLeague_id']){
            $ppLeague = $this->ppLeagueRepository->getOne($up['ppLeague_id']);      
            $up['rounds']= $up['ppTournamentType']['rounds'];
        }
        else if($up['ppCupGroup_id']){
            $ppCupGroup = $this->ppCupGroupRepository->getOne($up['ppCupGroup_id']);
            $up['levelFormat'] = $up['ppTournamentType']['cup_format'][$ppCupGroup['level'] - 1];
        }
        
        
        $column = $up['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';

        if(!$up['started']){
            $up['user_count']= $this->userParticipationRepository->count($column, $up[$column]);
        }

        if($up['started']){
            $up['currentRound_id'] = $this->ppRoundFindService->getCurrentRoundValue($column, $up[$column], 'id');
            $up['currentRound'] = $this->ppRoundFindService->getCurrentRoundValue($column, $up[$column], 'round');
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

    public function getOneByUserAndTournament(int $userId,  string $tournamentColumn, int $tournamentId, ?bool $enrich = true){
        $up = $this->userParticipationRepository->getOne(
            $userId, $tournamentColumn, $tournamentId
        );
        if($enrich)$this->enrich($up);
        return $up;
    }

   

    public function getUserPPDex(int $userId): array
    {
        $ppDex = [
            'ppCups' => [],
            'ppLeagues' => []
        ];

        // Helper function to fetch and populate tournament types
        $fetchAndPopulateTypes = function (bool $onlyCups) use (&$ppDex) {
            $tournamentTypes = $this->ppTournamentTypeFindService->get(null, $onlyCups, false, !$onlyCups);
            foreach ($tournamentTypes as $tournamentType) {
                if ($tournamentType['name'] !== 'MOTD') {
                    $key = $onlyCups ? 'ppCups' : 'ppLeagues';
                    if (!isset($ppDex[$key][$tournamentType['name']])) {
                        $ppDex[$key][$tournamentType['name']] = [];
                    }
                    $ppDex[$key][$tournamentType['name']][] = [
                        'ppTournamentType' => [
                            'id' => $tournamentType['id'],
                            'name' => $tournamentType['name'],
                            'level' => $tournamentType['level'],
                            'emoji' => $tournamentType['emoji'],
                            'is_cup' => $onlyCups,
                        ],
                        'userParticipation' => null
                    ];
                }
            }
        };

        // Fetch and populate tournament types for leagues and cups
        $fetchAndPopulateTypes(true);  // For Cups
        $fetchAndPopulateTypes(false); // For Leagues

        // Fetch user participations for leagues
        $leagueParticipations = $this->userParticipationRepository->getUserSchemaPPLeagues($userId);
        foreach ($leagueParticipations as $participation) {
            $name = $participation['pptt_name'];
            foreach ($ppDex['ppLeagues'][$name] as &$tournament) {
                if ($tournament['ppTournamentType']['id'] === $participation['pptt_id']) {
                    $tournament['userParticipation'] = [
                        'user_id' => $participation['up_user_id'],
                        'id' => $participation['up_id'],
                        'ppLeague_id' => $participation['up_ppLeague_id'],
                        'updated_at' => $participation['up_updated_at'],
                        'tot_points' => $participation['up_tot_points'],
                        'position' => $participation['up_position'],
                        'started_at' => $participation['up_started_at'],
                        'finished_at' => $participation['up_finished_at'],
                        'is_live' => isset($participation['up_started_at']) && is_null($participation['up_finished_at']),
                    ];
                }
            }
        }

        // Fetch user participations for cups
        $cupParticipations = $this->userParticipationRepository->getUserSchemaPPCups($userId);
        foreach ($cupParticipations as $participation) {
            $name = $participation['pptt_name'];
            foreach ($ppDex['ppCups'][$name] as &$tournament) {
                if ($tournament['ppTournamentType']['id'] === $participation['pptt_id']) {
                    $cupFormat = json_decode($participation['pptt_cup_format'], true);
                    $levelIndex = $participation['up_cup_level'] - 1;
                    $reachedLevelString = isset($cupFormat[$levelIndex]) ? $cupFormat[$levelIndex]['name'] : null;

                    $tournament['userParticipation'] = [
                        'user_id' => $participation['up_user_id'],
                        'id' => $participation['up_id'],
                        'ppCup_id' => $participation['up_ppCup_id'],
                        'ppCupGroup_id' => $participation['up_ppCupGroup_id'],
                        'updated_at' => $participation['up_updated_at'],
                        'tot_points' => $participation['up_tot_points'],
                        'position' => $participation['up_position'],
                        'started_at' => $participation['up_started_at'],
                        'finished_at' => $participation['up_finished_at'],
                        'is_live' => isset($participation['up_started_at']) && is_null($participation['up_finished_at']),
                        'reached_level' => $participation['up_cup_level'],
                        'reached_level_string' => $reachedLevelString,
                    ];
                }
            }
        }

        return $ppDex;
    }





}
