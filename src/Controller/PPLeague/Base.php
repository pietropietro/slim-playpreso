<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use App\Controller\BaseController;
use App\Service;
// use App\Service\PPLeagueType\FindPPT;
// use App\Service\UserParticipation\FindUP;
// use App\Service\PPRound\FindPPR;

abstract class Base extends BaseController
{
    protected function getPPLeagueService(): Service\PPLeague\Find
    {
        return $this->container->get('ppleague_service');
    }

    protected function getPPLeagueTypeService(): Service\PPLeagueType\Find
    {
        return $this->container->get('ppleaguetype_service');
    }

    protected function getParticipationService(): Service\UserParticipation\Find
    {
        return $this->container->get('user_participation_service');
    }

    protected function getPPRoundService(): Service\PPRound\Find
    {
        return $this->container->get('ppround_service');
    }

}
