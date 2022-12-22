<?php

declare(strict_types=1);

namespace App\Controller\PPRound;

use App\Controller\BaseController;
use App\Service\PPRound;

abstract class Base extends BaseController
{

    protected function getPPRoundFindService(): PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

}
