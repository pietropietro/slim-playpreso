<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

use App\Controller\BaseController;
use App\Service\PPTournamentType\Find;
use App\Service\UserParticipation\Create;
use App\Service\PPLeague;
use App\Service\User;

abstract class Base extends BaseController
{

    protected function getPPTournamentTypeService(): Find
    {
        return $this->container->get('pptournamenttype_find_service');
    }

    protected function getFindPPLeagueService(): PPLeague\Find
    {
        return $this->container->get('ppleague_find_service');
    }

    protected function getParticipationService(): Create
    {
        return $this->container->get('userparticipation_create_service');
    }

    protected function getPointsService(): User\Points
    {
        return $this->container->get('user_points_service');
    }


    protected function checkUserPermissions(int $userId, int $userIdLogged): void
    {
        if ($userId !== $userIdLogged) {
            throw new User('User permission failed.', 400);
        }
    }

}
