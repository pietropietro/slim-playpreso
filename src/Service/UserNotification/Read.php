<?php

declare(strict_types=1);

namespace App\Service\UserNotification;

use App\Service\BaseService;
use App\Repository\UserNotificationRepository;

final class Read extends BaseService{
    public function __construct(
        protected UserNotificationRepository $userNotificationRepository,
    ) {}

    public function setRead(int $userId, int $limit = null){
        return $this->userNotificationRepository->setRead($userId, $limit);
    }


}

