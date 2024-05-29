<?php

declare(strict_types=1);

namespace App\Service\League;

use App\Service\BaseService;
use App\Repository\LeagueRepository;
use App\Repository\TeamRepository;


final class Elaborate extends BaseService{
    public function __construct(
        protected LeagueRepository $leagueRepository,
        protected TeamRepository $teamRepository,
    ) {}

    public function setFetched(int $id){
        $this->leagueRepository->setFetched($id);
    }

    public function elaborateLsLeagueTable(array $ls_teams, int $league_id){
        $league_standings = [];
        foreach ($ls_teams as $key => $team_obj) {

            if(!$team_obj->Tid){
                throw new \App\Excepion\ExternalAPI("error while processing standings", 500);
            }
            $team_id = $this->teamRepository->getOne((int)$team_obj->Tid, true)['id'];

            $team = array(
                "id" => $team_id,
                "points" =>  $team_obj->pts,
                "position" =>  $team_obj->rnk,
                "played" =>  $team_obj->pld,
                "W" =>  $team_obj->win,
                "D" =>  $team_obj->drw,
                "L" =>  $team_obj->lst,
                "gf" => $team_obj->gf,
                "ga" => $team_obj->ga,
            );

            array_push($league_standings, $team); 
        }

        $this->leagueRepository->updateStandings($league_id, json_encode($league_standings));
    }

}