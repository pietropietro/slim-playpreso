<?php

declare(strict_types=1);

namespace App\Service\League;

use App\Service\RedisService;
use App\Repository\LeagueRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected LeagueRepository $leagueRepository,
    ){}

    public function getOne(int $id){
        return $this->leagueRepository->getOne($id);
    }

    public function getForPPLT(int $ppLTId){
        $ppLT =  $this->ppLeagueTypeRepository->getOne($ppLTId);
        if($ppLT['type'] === 'Europe'){
            $leagues = $this->leagueRepository->getForArea(strtolower($ppLT['type']), $ppLT['level']);
            $uefaLeagues =  $this->leagueRepository->getUefa();
            return array_merge($leagues,$uefaLeagues);
        }       

        $country = $ppLT['type'] === 'Random' ? null : strtolower($ppLT['type']);
        return $this->leagueRepository->getForCountry($country, $ppLT['level']);
    }
}