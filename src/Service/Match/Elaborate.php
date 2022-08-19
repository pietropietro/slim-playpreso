<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Repository\MatchRepository;
use App\Repository\TeamRepository;

final class Elaborate extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected TeamRepository $teamRepository,
    ) {}

    public function elaborateExternalData(array $lsEvents, int $league_id){
        $created_count = 0;
        foreach ($lsEvents as $key => $inputObj) {

            $ls_id = (int) $inputObj->Eid;

            if(!!$this->matchRepository->getOne($ls_id, true)){
                //UPDATE MATCH
                continue;
            }

            if($inputObj->Eps === 'FT') continue;

            $this->createMatch($inputObj, $league_id);
            $created_count++;
        }
        return $created_count;
    }

    private function createMatch(Object $inputObj, int $league_id){
        $home_id = $this->teamRepository->idFromExternal((int)$inputObj->T1[0]->ID);
        $away_id = $this->teamRepository->idFromExternal((int)$inputObj->T2[0]->ID);
        $round = (int)$inputObj->Ern;
        $this->matchRepository->create((int)$inputObj->Eid, $league_id, $home_id, $away_id, $round, (string)$inputObj->Esd);
    }

}