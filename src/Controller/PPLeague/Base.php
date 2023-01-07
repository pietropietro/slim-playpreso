<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use App\Controller\BaseController;
use App\Service;
// use App\Service\PPTournamentType\FindPPT;
// use App\Service\UserParticipation\FindUP;
// use App\Service\PPRound\FindPPR;

abstract class Base extends BaseController
{
    protected function getPPLeagueFindService(): Service\PPLeague\Find
    {
        return $this->container->get('ppleague_find_service');
    }

    protected function getUserParticipationFindService(): Service\UserParticipation\Find
    {
        return $this->container->get('userparticipation_find_service');
    }

    protected function getPPRoundFindService(): Service\PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

    protected function getMatchFindService(): Service\Match\Find
    {
        return $this->container->get('match_find_service');
    }

}
