<?php

declare(strict_types=1);

namespace App\Controller\PPLeagueType;

use App\Controller\BaseController;
use App\Service\PPLeagueType\Find;
use App\Service\UserParticipation\Create;
use App\Service\PPLeague;

abstract class Base extends BaseController
{

    protected function getPPLeagueTypeService(): Find
    {
        return $this->container->get('ppleaguetype_service');
    }

    protected function getPPLeagueService(): PPLeague\Find
    {
        return $this->container->get('ppleague_service');
    }

    protected function getParticipationService(): Create
    {
        return $this->container->get('user_participation_create_service');
    }


    protected function checkUserPermissions(int $userId, int $userIdLogged): void
    {
        if ($userId !== $userIdLogged) {
            throw new User('User permission failed.', 400);
        }
    }

}
