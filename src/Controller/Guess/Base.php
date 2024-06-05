<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use App\Controller\BaseController;
use App\Service\Guess;
use App\Service\League;
use App\Service\Match;
use App\Service\Team;

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


    protected function getTeamFindService(): Team\Find
    {
        return $this->container->get('team_find_service');
    }

}
