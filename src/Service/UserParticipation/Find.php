<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

use App\Service\RedisService;
use App\Service\BaseService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\PPRoundRepository;

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
        protected PPRoundRepository $ppRoundRepository
    ){}
    
    //TODO change type to ENUM
    public function getAll(int $userId, string $type, bool $active){
        $ups = $this->userParticipationRepository->getTypeParticipations($userId, $type.'_id', $active);        
        foreach($ups as $upKey => $upItem){
            if($type === 'ppLeague'){
                $ups[$upKey][$type.'Type'] = $this->ppLeagueTypeRepository->getOne($upItem['ppLeagueType_id']);
                $ups[$upKey][$type] = $this->ppLeagueRepository->getOne($upItem['ppLeague_id']);
                $ups[$upKey]['round_count'] = $this->ppRoundRepository->count('ppLeague_id',$upItem['ppLeague_id']);
                
            }
        }
        return $ups;
    }
}