<?php

declare(strict_types=1);

namespace App\Controller\Flash;

use App\Controller\BaseController;
use App\Service\Flash;
use App\Service\Guess;
use App\Service\Match;
use App\Service\User;

/**
 * Abstract base controller for Flash endpoints,
 * providing protected getters for services.
 */
abstract class Base extends BaseController
{
    protected function getFlashFindService(): Flash\Find
    {
        return $this->container->get('flash_find_service');
    }

    protected function getFlashCreateService(): Flash\Create
    {
        return $this->container->get('flash_create_service');
    }

    protected function getGuessFindService(): Guess\Find
    {
        return $this->container->get('guess_find_service');
    }

    protected function getGuessCreateService(): Guess\Create
    {
        return $this->container->get('guess_create_service');
    }

    protected function getMatchFindService(): Match\Find
    {
        return $this->container->get('match_find_service');
    }

    protected function getUserFindService(): User\Find
    {
        return $this->container->get('user_find_service');
    }
}
