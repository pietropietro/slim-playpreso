<?php

declare(strict_types=1);

namespace App\Controller\PPRound;

use App\Controller\BaseController;
use App\Service\PPRound;
use App\Service\PPLeague;
use App\Service\PPCupGroup;

abstract class Base extends BaseController
{

    protected function getPPRoundFindService(): PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

    protected function getPPRoundCreateService(): PPRound\Create
    {
        return $this->container->get('ppround_create_service');
    }


    protected function getPPLeagueFindService(): PPLeague\Find
    {
        return $this->container->get('ppleague_find_service');
    }

    protected function getPPCupGroupFindService(): PPCupGroup\Find
    {
        return $this->container->get('ppcupgroup_find_service');
    }

    

}
