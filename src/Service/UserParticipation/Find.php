<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

use App\Service\RedisService;
use App\Service\BaseService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\PPRoundRepository;
use App\Repository\GuessRepository;

// enum Suit{
//         case Hearts;
//         case Diamonds;
//         case Clubs;
//         case Spades;
//     }
final class Find  extends BaseService{

    
    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPRoundRepository $ppRoundRepository,
        protected GuessRepository $guessRepository
        
    ){}
    
    //TODO change playMode to ENUM
    public function getAll(int $userId, string $playMode, bool $active){
        $ups = $this->userParticipationRepository->getTypeParticipations($userId, $playMode.'_id', $active);        
        foreach($ups as $upKey => $upItem){
            if($playMode === 'ppLeague'){
                $ups[$upKey][$playMode.'Type'] = $this->ppLeagueTypeRepository->getOne($upItem['ppLeagueType_id']);
                $ups[$upKey][$playMode] = $this->ppLeagueRepository->getOne($upItem['ppLeague_id']);                
                $ups[$upKey]['locked'] = !$this->guessRepository->hasUnlockedGuesses($userId, $playMode.'_id',$upItem['ppLeague_id']);                
            }
        }
        return $ups;
    }
}