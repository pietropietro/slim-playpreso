<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\RedisService;
use App\Repository\PPLeagueRepository;
use App\Service\BaseService;

final class Update  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueRepository $ppLeagueRepository,
    ) {}

}
