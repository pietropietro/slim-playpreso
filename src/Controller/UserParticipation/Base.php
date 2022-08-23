<?php

declare(strict_types=1);

namespace App\Controller\UserParticipation;

use App\Controller\BaseController;
use App\Service\UserParticipation\Find;

abstract class Base extends BaseController
{
    protected function getParticipationService(): Find
    {
        return $this->container->get('userparticipation_find_service');
    }

}
