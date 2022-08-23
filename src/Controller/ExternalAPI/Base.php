<?php

declare(strict_types=1);

namespace App\Controller\ExternalAPI;

use App\Controller\BaseController;
use App\Service\ExternalAPI\Call;
use App\Service\League;

abstract class Base extends BaseController
{
    protected function getExternalApiService(): Call
    {
        return $this->container->get('external_api_service');
    }

    protected function getLeaguesService(): League\Find
    {
        return $this->container->get('league_find_service');
    }

}
