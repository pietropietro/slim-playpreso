<?php

declare(strict_types=1);

namespace App\Controller\PPLeagueType;

use App\Controller\BaseController;
use App\Service\PPLeagueType\Find;

abstract class Base extends BaseController
{

    protected function getPPLeagueTypeService(): Find
    {
        return $this->container->get('ppleaguetype_service');
    }

    protected function checkUserPermissions(int $userId, int $userIdLogged): void
    {
        if ($userId !== $userIdLogged) {
            throw new User('User permission failed.', 400);
        }
    }

}
