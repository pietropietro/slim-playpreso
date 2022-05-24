<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

use App\Service\RedisService;
use App\Service\BaseService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPLeagueTypeRepository;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository
    ){}

    public function getAll($userId, $type, $active){
        $ups = $this->userParticipationRepository->getTypeParticipations($userId, $type, $active);        
        foreach($ups as $upKey => $upItem){
            $ups[$upKey]['ppLType'] = $this->ppLeagueTypeRepository->getOne($upItem['ppLeagueType_id']);
        }
        return $ups;
    }
}