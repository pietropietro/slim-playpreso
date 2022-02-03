<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use App\Controller\BaseController;
use App\Exception\Guess;
use App\Service\Guess\GuessService;

abstract class Base extends BaseController
{
    protected function getGuessService(): GuessService
    {
        return $this->container->get('guess_service');
    }

    //TODO understand what it does
    protected function getAndValidateUserId(array $input): int
    {
        if (isset($input['decoded']) && isset($input['decoded']->sub)) {
            return (int) $input['decoded']->sub;
        }

        throw new Guess('Invalid user. Permission failed.', 400);
    }
}
