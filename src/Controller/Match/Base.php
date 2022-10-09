<?php

declare(strict_types=1);

namespace App\Controller\Match;

use App\Controller\BaseController;
use App\Service\Match;


abstract class Base extends BaseController
{
    protected function getFindMatchService(): Match\Find
    {
        return $this->container->get('match_find_service');
    } 

}
