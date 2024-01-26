<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Service\Points;
use App\Service\PushNotifications;
use App\Repository\GuessRepository;


final class Verify extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected Points\Calculate $pointsCalculateService,
        protected Points\Update $pointsUpdateService,
        protected PushNotifications\Send $pushNotificationsService
    ){}

    public function verify(int $matchId, int $scoreHome, int $scoreAway){
        $guesses = $this->guessRepository->getForMatch($matchId, not_verified: false);

        foreach ($guesses as $key => $guess) {
            $result = $this->pointsCalculateService->calculate($scoreHome,$scoreAway,$guess['home'],$guess['away']);
            $this->guessRepository->verify($guess['id'], $result['unox2'], $result['uo25'], $result['ggng'], $result['preso'], $result['points']);
            $this->pointsUpdateService->plus($guess['user_id'], $result['points']);

            //TODO Add specific values
            try {
                if($guess['guessed_at']){
                    $title = (string) $result['points'];
                    $body = (string)  $guess['id'];
                    $this->pushNotificationsService->send($guess['user_id'], $title, $body);
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function setMissed(){
        $this->guessRepository->verifyMissed();
    }

}