<?php

declare(strict_types=1);

namespace App\Service\Team;

use App\Service\BaseService;
use App\Repository\TeamRepository;


final class Elaborate extends BaseService{
    public function __construct(
        protected TeamRepository $teamRepository,
    ) {}

    public function insertTeams(array $ls_teams, $country = ''){        
        foreach ($ls_teams as $key => $team_obj) {
            if(!$team_obj->Tid){
                throw new \App\Excepion\ExternalAPI("error teams", 500);
            }
            if($this->teamRepository->getOne(id: (int)$team_obj->Tid, is_external_id: true)) continue;
            $this->teamRepository->create(ls_id: (int)$team_obj->Tid, name: $team_obj->Tnm, country: $country);
        }
    }
}
