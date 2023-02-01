<?php

declare(strict_types=1);

namespace App\Controller\EmailPreferences;

use App\Controller\BaseController;
use App\Service\EmailPreferences;

abstract class Base extends BaseController
{
    protected function getUpdateEmailPreferencesService(): EmailPreferences\Update
    {
        return $this->container->get('emailpreferences_update_service');
    }

}
