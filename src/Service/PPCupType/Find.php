<?php

declare(strict_types=1);

namespace App\Service\PPCupType;

use App\Service\BaseService;
use App\Service\RedisService;
use App\Repository\PPCupTypeRepository;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPCupTypeRepository $ppCupTypeRepository,
    ){}

    public function getOne(int $id){
        $ppCT =  $this->ppCupTypeRepository->getOne($id);
        //TODO
        // $ppCT['leagues'] = $this->leagueService->getForPPCT($id);
        return $ppCT;
    }
}