<?php

declare(strict_types=1);

namespace App\Controller\PPRoundMatch;

use App\Controller\BaseController;
use App\Service\PPRoundMatch;
use App\Service\PPRound;

abstract class Base extends BaseController
{

    protected function getPPRoundMatchUpdateService(): PPRoundMatch\Update
    {
        return $this->container->get('pproundmatch_update_service');
    }

    protected function getPPRoundMatchCreateService(): PPRoundMatch\Create
    {
        return $this->container->get('pproundmatch_create_service');
    }

    protected function getPPRoundFindService(): PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

}
