<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

use App\Service\BaseService;
use App\Service\PPTournament;
use App\Repository\UserParticipationRepository;

final class Create extends BaseService
{
    public function __construct(
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournament\VerifyAfterJoin $ppTournamentVerifyService,
    ) {}

    public function create(
        int $userId, 
        int $ppTournamentTypeId, 
        int $ppTournamentId, 
        ?int $ppGroupId = null, 
        ?string $fromTag = null)
    {

        if($this->userParticipationRepository->isUserInTournament(
                $userId, 
                $ppGroupId ? 'ppCupGroup_id' : 'ppLeague_id',
                $ppGroupId ?? $ppTournamentId, 
        ))return;

        $columns = $ppGroupId ? array("ppCup_id", "ppCupGroup_id", "ppTournamentType_id", "from_tag") : array("ppLeague_id", "ppTournamentType_id");
        $valueIds = $ppGroupId ? array($ppTournamentId, $ppGroupId, $ppTournamentTypeId, $fromTag) : array($ppTournamentId, $ppTournamentTypeId);

        if(!$participation = $this->userParticipationRepository->create($userId, $columns, $valueIds)){
            throw new \App\Exception\User('cant join',500);
        }

        $tournamentColumn = $ppGroupId ? 'ppCupGroup_id' : 'ppLeague_id';
        $this->ppTournamentVerifyService->afterJoined($tournamentColumn, $ppGroupId ?? $ppTournamentId, $ppTournamentTypeId);

        return $participation;
    }
}