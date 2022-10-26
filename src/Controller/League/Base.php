<?php

declare(strict_types=1);

namespace App\Controller\League;

use App\Controller\BaseController;
use App\Service\League;


abstract class Base extends BaseController
{
    protected function getFindLeagueService(): League\Find
    {
        return $this->container->get('league_find_service');
    }
    
}
