<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use App\Controller\BaseController;
use App\Service\PPRoundMatch;
use App\Service\Guess;
use App\Service\Match;
use App\Service\MOTD;
use App\Service\PPTournamentType;
use App\Service\User;


abstract class Base extends BaseController
{
    protected function getMotdFindService(): MOTD\Find
    {
        return $this->container->get('motd_find_service');
    } 

    protected function getDeletePPRoundMatchService(): PPRoundMatch\Delete
    {
        return $this->container->get('pproundmatch_delete_service');
    }

    protected function getPPRoundMatchFindService(): PPRoundMatch\Find
    {
        return $this->container->get('pproundmatch_find_service');
    }

    protected function getMotdCreateService(): MOTD\Create
    {
        return $this->container->get('motd_create_service');
    }

    protected function getMatchFindService(): Match\Find
    {
        return $this->container->get('match_find_service');
    } 

    protected function getGuessCreateService(): Guess\Create
    {
        return $this->container->get('guess_create_service');
    }

    protected function getGuessLockService(): Guess\Lock
    {
        return $this->container->get('guess_lock_service');
    }

    protected function getPPTournamentTypeFindService(): PPTournamentType\Find
    {
        return $this->container->get('pptournamenttype_find_service');
    }

    protected function getMotdLeaderService(): MOTD\Leader
    {
        return $this->container->get('motd_leader_service');
    }

    protected function getUserFindService(): User\Find
    {
        return $this->container->get('user_find_service');
    }

    

}
