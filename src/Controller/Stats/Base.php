<?php

declare(strict_types=1);

namespace App\Controller\Stats;

use App\Controller\BaseController;
use App\Service\Stats;
use App\Service\PPRanking;
use App\Service\Trophy;
use App\Repository\UserRepository;

abstract class Base extends BaseController
{

    protected function getFindStatsService(): Stats\Find
    {
        return $this->container->get('stats_find_service');
    }

    protected function getStatsUserService(): Stats\User
    {
        return $this->container->get('stats_user_service');
    }

    protected function getPPRankingFindService(): PPRanking\Find
    {
        return $this->container->get('ppranking_find_service');
    }

    protected function getTrophyFindService(): Trophy\Find
    {
        return $this->container->get('trophy_find_service');
    }


    protected function getUserRepository(): UserRepository
    {
        return $this->container->get('user_repository');
    }


}
