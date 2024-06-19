<?php

declare(strict_types=1);

namespace App\Controller\PPRanking;

use App\Controller\BaseController;
use App\Service\PPRanking;
use App\Service\User;

abstract class Base extends BaseController
{

    protected function getPPRankingFindService(): PPRanking\Find
    {
        return $this->container->get('ppranking_find_service');
    }

    protected function getUserFindService(): User\Find
    {
        return $this->container->get('user_find_service');
    }
}
