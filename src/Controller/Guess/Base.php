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

}
