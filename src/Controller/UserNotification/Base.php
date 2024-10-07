<?php

declare(strict_types=1);

namespace App\Controller\UserNotification;

use App\Controller\BaseController;
use App\Service\UserNotification;

abstract class Base extends BaseController
{
    protected function getUserNotificationFindService(): UserNotification\Find
    {
        return $this->container->get('usernotification_find_service');
    }
    protected function getUserNotificationReadService(): UserNotification\Read
    {
        return $this->container->get('usernotification_read_service');
    }

    

}
