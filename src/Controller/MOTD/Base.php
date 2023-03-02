<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use App\Controller\BaseController;
use App\Service\PPRoundMatch;
use App\Service\Guess;
use App\Service\Match;
use App\Service\MOTD;


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

}
