<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\BaseController;
use App\Exception\User;
use App\Service\User\Create;
use App\Service\User\Delete;
use App\Service\User\Find;
use App\Service\User\Login;
use App\Service\User\Update;
use App\Service\UserParticipation;

abstract class Base extends BaseController
{
    protected function getFindUserService(): Find
    {
        return $this->container->get('find_user_service');
    }

    protected function getCreateUserService(): Create
    {
        return $this->container->get('create_user_service');
    }

    protected function getUpdateUserService(): Update
    {
        return $this->container->get('update_user_service');
    }

    protected function getDeleteUserService(): Delete
    {
        return $this->container->get('delete_user_service');
    }

    protected function getLoginUserService(): Login
    {
        return $this->container->get('login_user_service');
    }

    protected function getParticipationService(): UserParticipation\Find
    {
        return $this->container->get('userparticipation_service');
    }

    protected function checkUserPermissions(int $userId, int $userIdLogged): void
    {
        if ($userId !== $userIdLogged) {
            throw new User('User permission failed.', 400);
        }
    }

}
