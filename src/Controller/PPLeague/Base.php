<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use App\Controller\BaseController;
use App\Service\PPLeague\Find;

abstract class Base extends BaseController
{
    protected function getPPLeagueService(): Find
    {
        return $this->container->get('ppleague_service');
    }

}
