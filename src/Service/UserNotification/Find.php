<?php

declare(strict_types=1);

namespace App\Service\UserNotification;

use App\Service\BaseService;
use App\Service\Guess;
use App\Service\UserParticipation;
use App\Repository\UserNotificationRepository;

final class Find extends BaseService{
    public function __construct(
        protected UserNotificationRepository $userNotificationRepository,
        protected Guess\Find $guessFindService,       
        protected UserParticipation\Find $userParticipationFindService,       
    ) {}

    public function getUnread(int $userId, ?bool $enriched=false){
        $un = $this->userNotificationRepository->getUnread($userId);
        if($enriched)$this->enrich($un);
        return $un;
    }

    public function getForUser(int $userId){
        $notifications = $this->userNotificationRepository->getForUser($userId);
        $this->enrich($notifications);
        return $notifications;
    }

    private function enrich(&$notifications){
        foreach ($notifications as &$n) {
            if(in_array(
                $n['event_type'], 
                ['guess_verified', 'guess_unlocked_starting'])
            ){
                $n['guess'] = $this->guessFindService->getOne($n['event_id']);
            } 
            else if($n['event_type'] == 'ppleague_finished'){
                $n['userParticipation'] = $this->userParticipationFindService->getOne($n['event_id'], true);
            }
        }
    }
    
}

