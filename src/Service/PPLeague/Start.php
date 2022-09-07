<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Repository\PPLeagueRepository;
use App\Service\BaseService;
use App\Service\PPRound\Create;
use App\Service\UserParticipation\Update;

final class Start  extends BaseService{
    public function __construct(
        protected PPLeagueRepository $ppLeagueRepository,
        protected Create $createPPRoundService,
        protected Update $updateUPService,
    ) {}

    public function start(): bool{
        $this->ppLeagueRepository->start($id);
        if($this->createPPRoundService->create('ppLeague_id', $id, 1)) return true;
        return false;
    }

}
