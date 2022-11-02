<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

use App\Service\BaseService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPLeagueRepository;

final class Create extends BaseService
{

    public function __construct(
        protected UserParticipationRepository $userParticipationRepository,
        protected PPLeagueRepository $ppLeagueRepository,
    ) {}

    public function create(int $userId, int $ppTournamentTypeId, int $ppTournamentId, ?int $ppGroupId = null)
    {
        $columns = $ppGroupId ? array("ppCup_id", "ppCupGroup_id", "ppTournamentType_id") : array("ppLeague_id", "ppTournamentType_id");
        $valueIds = $ppGroupId ? array($ppTournamentId, $ppGroupId, $ppTournamentTypeId) : array($ppTournamentId, $ppTournamentTypeId);

        if(!$participation = $this->userParticipationRepository->create($userId, $columns, $valueIds)){
            throw new \App\Exception\User('cant join',500);
        }

        //TODO delete
        if($ppGroupId){
        }else{
            $this->ppLeagueRepository->incUserCount($ppTournamentId);
        }

        return $participation;
    }
}