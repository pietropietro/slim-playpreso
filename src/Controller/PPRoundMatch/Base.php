<?php

declare(strict_types=1);

namespace App\Controller\PPRoundMatch;

use App\Controller\BaseController;
use App\Service\PPRoundMatch;

abstract class Base extends BaseController
{

    protected function getPPRoundMatchUpdateService(): PPRoundMatch\Update
    {
        return $this->container->get('pproundmatch_update_service');
    }

}
