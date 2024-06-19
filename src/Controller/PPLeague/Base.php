<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use App\Controller\BaseController;
use App\Service\PPTournamentType;
use App\Service\PPLeague;
use App\Service\UserParticipation;
use App\Service\PPRound;
use App\Service\Match;
use App\Service\User;

abstract class Base extends BaseController
{
    protected function getPPLeagueFindService(): PPLeague\Find
    {
        return $this->container->get('ppleague_find_service');
    }

    protected function getPPTournamentTypeFindService(): PPTournamentType\Find
    {
        return $this->container->get('pptournamenttype_find_service');
    }

    protected function getUserParticipationFindService(): UserParticipation\Find
    {
        return $this->container->get('userparticipation_find_service');
    }

    protected function getPPRoundFindService(): PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

    protected function getMatchFindService(): Match\Find
    {
        return $this->container->get('match_find_service');
    }

    protected function getUserFindService(): User\Find
    {
        return $this->container->get('user_find_service');
    }

}
