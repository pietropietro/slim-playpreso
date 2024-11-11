<?php

declare(strict_types=1);

namespace App\Service\UserNotification;

use App\Service\Guess;
use App\Service\PushNotifications;
use App\Repository\UserNotificationRepository;
use App\Repository\PushNotificationPreferencesRepository;

final class Create extends Base
{
    public function __construct(
        protected UserNotificationRepository $userNotificationRepository,   
        protected PushNotificationPreferencesRepository $pushNotificationPreferencesRepository,   
        protected Guess\Find $guessFindService,       
        protected PushNotifications\Send $pushNotificationsService, 
    ) {}

    public function create(
        int $userId, 
        string $eventType, 
        int $eventId, 
        ?array $push_text_data = null
    ){
        $allowed_events = $this->getAllowedEvents();
        if(!in_array($eventType, $allowed_events)) return;
        //if notification was already created, return;
        if($this->userNotificationRepository->has($userId, $eventType, $eventId)) return;
        
        //INTERNAL USER NOTIFICATION
        //1. create internal notification row
        $this->userNotificationRepository->create($userId, $eventType, $eventId);
        
        //PUSH NOTIFICATION
        //check if user has token
        if(!$this->pushNotificationsService->hasToken($userId)) return;
        //check if user has disabled this notification
        if($this->pushNotificationPreferencesRepository->hasRejected($userId, $eventType)) return;


        //send out push notification 
        if ($push_text_data == null){
            if($eventType == 'guess_verified'){
                $push_text_data = $this->getGuessVerifiedPushData($eventId);
            }
            else if($eventType == 'guess_unlocked_starting'){
                $push_text_data = $this->getGuessUnlockedStartingPushData($eventId);
            }
        }
        
        $this->pushNotificationsService->send($userId, $push_text_data['title'], $push_text_data['body']);
    }

    public function getAllowedEvents(){
        return ['guess_verified', 'ppleague_finished', 'guess_unlocked_starting'];
    }


    
    private function getGuessVerifiedPushData(int $guessId){
        //REDIS THIS
        $guess = $this->guessFindService->getOne($guessId);
        $teamNames = $guess['match']['homeTeam']['name']. ' - ' . $guess['match']['awayTeam']['name'];
        $realScore = $guess['match']['score_home'] . '-' . $guess['match']['score_away'];
        $guessedScore = $guess['home'] . '-' . $guess['away'];

        $title = $guess['ppTournamentType']['emoji']." ".$teamNames;

        $body = '🏁 '. $realScore;
        if($guess['PRESO']){$body .= ' PRESO!';}
        else if(!$guess['guessed_at']){$body .= ' ❌';}
        else{$body .= ' 🔒 ' . $guessedScore . '   🅿️ ' . $guess['points'];}
        
        return array(
            'title' => $title,
            'body' => $body
        );
    }

    private function getGuessUnlockedStartingPushData(int $guessId){
        $guess = $this->guessFindService->getOne($guessId);
        $teamNames = $guess['match']['homeTeam']['name']. ' - ' . $guess['match']['awayTeam']['name'];

        $body = $teamNames . ' is starting soon';
        
        return array(
            'title' => 'LOCK REMINDER',
            'body' =>  $body
        );
    }
    
}