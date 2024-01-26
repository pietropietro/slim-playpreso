<?php

declare(strict_types=1);

namespace App\Controller\DeviceToken;

use App\Controller\BaseController;
use App\Repository\DeviceTokenRepository;

abstract class Base extends BaseController
{
    protected function getDeviceTokenRepository(): DeviceTokenRepository
    {
        return $this->container->get('devicetoken_repository');
    }

}
