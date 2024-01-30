<?php

declare(strict_types=1);

namespace App\Service\UserNotification;

use App\Service\BaseService;
use App\Service\Guess;
use App\Repository\UserNotificationRepository;

final class Find extends BaseService{
    public function __construct(
        protected UserNotificationRepository $userNotificationRepository,
        protected Guess\Find $guessFindService,       
    ) {}

    public function getForUser(int $userId){
        $notifications = $this->userNotificationRepository->getForUser($userId);
        $this->enrich($notifications);
        return $notifications;
    }

    private function enrich(&$notifications){
        foreach ($notifications as &$n) {
           if($n['event_type'] == 'guess_verified'){
            $n['guess'] = $this->guessFindService->getOne($n['event_id']);
           } 
        }
    }
    
}

