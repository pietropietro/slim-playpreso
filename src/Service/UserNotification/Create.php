<?php

declare(strict_types=1);

namespace App\Service\UserNotification;

use App\Service\Guess;
use App\Service\PushNotifications;
use App\Repository\UserNotificationRepository;

final class Create extends Base
{
    public function __construct(
        protected UserNotificationRepository $userNotificationRepository,   
        protected Guess\Find $guessFindService,       
        protected PushNotifications\Send $pushNotificationsService, 
    ) {}

    public function create(
        int $userId, 
        string $eventType, 
        int $eventId, 
    ){
        $allowed_events = ['guess_verified'];
        if(!in_array($eventType, $allowed_events)) return;
        if($this->userNotificationRepository->has($userId, $eventType, $eventId)) return;
        
        //1. create internal notification row
        $this->userNotificationRepository->create($userId, $eventType, $eventId);
        
        //2. send out push notification (if registered)
        if(!$this->pushNotificationsService->hasToken($userId)) return;
        $push_text_data = null;
        if($eventType == 'guess_verified'){
            $push_text_data = $this->getGuessVerifiedPushData($eventId);
        }
        $this->pushNotificationsService->send($userId, $push_text_data['title'], $push_text_data['body']);
    }

    private function getGuessVerifiedPushData(int $guessId){
        //REDIS THIS
        $guess = $this->guessFindService->getOne($guessId);
        $teamNames = $guess['match']['homeTeam']['name']. ' - ' . $guess['match']['awayTeam']['name'];
        $realScore = $guess['match']['score_home'] . '-' . $guess['match']['score_away'];
        $guessedScore = $guess['home'] . '-' . $guess['away'];

        $title = $teamNames . ' ' . $realScore;
        
        if($guess['PRESO']){$body = 'PRESO!';}
        else if(!$guess['guessed_at']){$body = '❌';}
        else{$body = 'your lock: ' . $guessedScore . ' +' . $guess['points'];}
        
        return array(
            'title' => $title,
            'body' => $body
        );
    }
}