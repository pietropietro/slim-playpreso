<?php

declare(strict_types=1);

namespace App\Controller\UserNotification;

use App\Controller\BaseController;
use App\Service\UserNotification\Find;

abstract class Base extends BaseController
{
    protected function getUserNotificationFindService(): Find
    {
        return $this->container->get('usernotification_find_service');
    }

}
