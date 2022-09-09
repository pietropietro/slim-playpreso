<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use App\Controller\BaseController;
use App\Service\Guess;

abstract class Base extends BaseController
{
    protected function getLockService(): Guess\Lock
    {
        return $this->container->get('guess_lock_service');
    }

}
