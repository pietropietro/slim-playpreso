<?php

declare(strict_types=1);

namespace App\Controller\PushNotificationPreferences;

use App\Controller\BaseController;
use App\Repository\PushNotificationPreferencesRepository;
use App\Service\UserNotification;

abstract class Base extends BaseController
{
    protected function getPushNotificationPreferencesRepository(): PushNotificationPreferencesRepository
    {
        return $this->container->get('pushnotificationpreferences_repository');
    }

    protected function getUserNotificationCreateService(): UserNotification\Create
    {
        return $this->container->get('usernotification_create_service');
    }

}
