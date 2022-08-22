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


    public function createPPLeagueParticipation(int $userId, int $ppLeagueId, int $ppLeagueTypeId)
    {

        $columns = array("ppLeague_id", "ppLeagueType_id");
        $valueIds = array($ppLeagueId, $ppLeagueTypeId);

        if(!$participation = $this->userParticipationRepository->create($userId, $columns, $valueIds)){
            throw new \App\Exception\User('cant join',500);
        }

        $this->ppLeagueRepository->incUserCount($ppLeagueId);

        return $participation;

    }
}