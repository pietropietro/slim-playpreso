<?php

declare(strict_types=1);

namespace App\Controller\Stats;

use App\Controller\BaseController;
use App\Service\Stats;

abstract class Base extends BaseController
{

    protected function getFindStatsService(): Stats\Find
    {
        return $this->container->get('stats_find_service');
    }


}
