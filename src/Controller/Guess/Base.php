<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use App\Controller\BaseController;
use App\Service\Guess;
use App\Service\League;
use App\Service\Match;
use App\Service\Team;
use App\Service\MOTD;
use App\Service\PPRound;
use App\Service\UserParticipation;

abstract class Base extends BaseController
{
    protected function getLockService(): Guess\Lock
    {
        return $this->container->get('guess_lock_service');
    }

    protected function getGuessFindService(): Guess\Find
    {
        return $this->container->get('guess_find_service');
    }

    protected function getMatchFindService(): Match\Find
    {
        return $this->container->get('match_find_service');
    }

    protected function getLeagueFindService(): League\Find
    {
        return $this->container->get('league_find_service');
    }

    protected function getPPRoundFindService(): PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

    protected function getTeamFindService(): Team\Find
    {
        return $this->container->get('team_find_service');
    }

    protected function getMotdFindService(): MOTD\Find
    {
        return $this->container->get('motd_find_service');
    }

    protected function getUserParticipationFindService(): UserParticipation\Find
    {
        return $this->container->get('userparticipation_find_service');
    }

}
