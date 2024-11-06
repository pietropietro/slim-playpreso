<?php

declare(strict_types=1);

namespace App\Service\Highlights;

use App\Service\RedisService;
use App\Repository\HighlightsRepository;
use App\Repository\GuessRepository;
use App\Service\Match;
use App\Service\PPTournamentType;
use App\Service\User;

final class LastPresos  extends Base{
    public function __construct(
        protected RedisService $redisService,
        protected HighlightsRepository $highlightsRepository,
        protected GuessRepository $guessRepository,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
        protected Match\Find $matchFindService,
        protected User\Find $userFindService,
    ){}

    private const REDIS_KEY_PRESO_HIGHLIGHTS = 'highlights-preso-limit:%d';
    public function getLastPresos(int $limit){
        if (self::isRedisEnabled() === true ) {
            $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_PRESO_HIGHLIGHTS, $limit));
            $cached = $this->redisService->get($redisKey); // This returns null if not found or the user data if found
            if($cached !== null)return $cached;
        }

        $lastPresos = $this->calculateLastPresos($limit);

        if (self::isRedisEnabled() === true ) {
            $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_PRESO_HIGHLIGHTS, $limit));
            $expiration = 1 * 60 * 60; 
            $this->redisService->setex($redisKey, $lastPreso, $expiration); 
        }
        return $lastPresos;
    }

    private function calculateLastPresos(?int $limit=1) {
        $presosSummary = $this->highlightsRepository->getLastPresos($limit);
        
        foreach ($presosSummary as $key => $value) {
            $presosSummary[$key] = $this->buildMatchGuessesPair($value);
        }

        return $presosSummary;
        
    }

    private function buildMatchGuessesPair($summary){
        $match = $this->matchFindService->getOne(
            $summary['match_id'],
            withStats: false
        );
        $guesses = $this->guessRepository->get(explode(",", $summary['ids']));

        foreach ($guesses as &$guess) {
           $guess['ppTournamentType'] = $this->ppTournamentTypeFindService->getFromPPRoundMatch($guess['ppRoundMatch_id']);
           $guess['user'] = $this->userFindService->getOne($guess['user_id']);
        }

        // $tot_locks = $this->guessRepository->countForMatch($match['id']);
        return array(
            'match'=> $match,
            'guesses' => $guesses,
        );
    }
}