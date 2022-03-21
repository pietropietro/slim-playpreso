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

    protected function getAndValidateUserId(array $input): int
    {
        if (isset($input['JWT_decoded']) && isset($input['JWT_decoded']->id)) {
            return (int) $input['JWT_decoded']->id;
        }

        throw new User('Invalid user. Permission failed.', 400);
    }
}
