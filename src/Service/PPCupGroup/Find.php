<?php

declare(strict_types=1);

namespace App\Service\PPCupGroup;

use App\Service\RedisService;
use App\Repository\PPCupGroupRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPCupGroupRepository $ppCupGroupRepository,

    ) {}

    public function getOne($id){
        return $this->ppCupGroupRepository->getOne($id);
    }
}
