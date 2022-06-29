<?php

declare(strict_types=1);

namespace App\Controller\PPCup;

use App\Controller\BaseController;
use App\Service;


abstract class Base extends BaseController
{
    protected function getCupCountService(): Service\PPCup\Count
    {
        return $this->container->get('ppcup_count_service');
    }    
}
