<?php

declare(strict_types=1);

namespace App\Service\Highlights;

use App\Service\RedisService;
use App\Service\BaseService;

abstract class Base extends BaseService
{

    public function __construct(
        protected RedisService $redisService
    ) {
    }

}
