<?php

declare(strict_types=1);

namespace App\Service\UserNotification;

use App\Service\BaseService;
use App\Service\Guess;
use App\Repository\UserNotificationRepository;

abstract class Base extends BaseService
{
    public function __construct(
        protected UserNotificationRepository $userNotificationRepository,  
        protected Guess\Find $guessFindService      
    ) {
    }


}
