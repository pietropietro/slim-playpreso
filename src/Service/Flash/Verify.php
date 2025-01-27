<?php

declare(strict_types=1);

namespace App\Service\Flash;

use App\Service\BaseService;
use App\Service\Points;
use App\Repository\FlashRepository;
use App\Repository\GuessRepository;

final class Verify extends BaseService{
    public function __construct(
        protected FlashRepository $flashRepository,
        protected GuessRepository $guessRepository,
        protected Points\Update $pointsUpdateService,
    ) {}
    
    public function checkIfVerify(int $matchId){
        $pprmFlash = $this->flashRepository->getWithMatch($matchId);
        if(!$pprmFlash)return;

        $guesses = $this->guessRepository->getForPPRoundMatch($pprmFlash['id']);
        if (empty($guesses)) {
            // No one guessed => no winners
            return;
        }

        $jackpot = $pprmFlash['lock_cost'] * count($guesses);

        $this->pointsUpdateService->payOutJackpot($guesses, $jackpot);

        //calculate leader
        $this->calculateFlashLeader($pprmFlash['id']);
    }

    private function calculateFlashLeader(int $after_pprm_id){
        $chart = $this->flashRepository->retrieveFlashChart()['chart'];
        if(!$chart[0])return;
        $this->flashRepository->insertLeader($chart[0]['user_id'], (int) $chart[0]['tot_points'], $after_pprm_id);
        return $chart[0]['user_id'];
    }

}