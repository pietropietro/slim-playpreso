<?php

declare(strict_types=1);

namespace App\Controller\League;

use App\Controller\BaseController;
use App\Service\League;
use App\Service\Match;
use App\Service\ExternalAPI;


abstract class Base extends BaseController
{
    protected function getFindLeagueService(): League\Find
    {
        return $this->container->get('league_find_service');
    }

    protected function getMatchFindService(): Match\Find
    {
        return $this->container->get('match_find_service');
    }
    
    protected function getExternalApiService(): ExternalAPI\Call
    {
        return $this->container->get('external_api_service');
    }

    protected function getUpdateLeagueService(): League\Update
    {
        return $this->container->get('league_update_service');
    }

    protected function getCreateLeagueService(): League\Create
    {
        return $this->container->get('league_create_service');
    }

}
