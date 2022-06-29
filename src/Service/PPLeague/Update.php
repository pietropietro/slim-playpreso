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

    //used to update schema changes
    private function upddatePPLsRoundCount(){
        $ids = $this->ppLeagueRepository->startedIds();
        foreach($ids as $id){
            $round_count = $this->ppRoundRepository->count('ppLeague_id',$id);
            $this->ppLeagueRepository->updateValue($id, 'round_count', $round_count);
        }
    }

}
