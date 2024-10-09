<?php

declare(strict_types=1);

namespace App\Controller\Match;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminPick extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $ppTournamentTypeId = (int) $args['id'];
        $ppTournamentType = $this->getPPTournamentTypeFindService()->getOne($ppTournamentTypeId);
        if($ppTournamentType['name']=='MOTD'){
            $all_matches_raw = $this->getPickMatchService()->pickForToday(null);
            $pickedMatchesRaw = array($all_matches_raw[0]);
            $leagues = array();
        }else{
            $pickedMatchesRaw = $this->getPickMatchService()->pick($ppTournamentTypeId, 3);
            $all_matches_raw = $this->getPickMatchService()->nextMatchesForPPTournamentType($ppTournamentTypeId, 10); 
            $leagues =  $this->getLeagueFindService()->getForPPTournamentType($ppTournamentTypeId, true);
        }

        $ids = array_column($pickedMatchesRaw, 'id');
        $all_ids = array_column($all_matches_raw, 'id');

        $pickedMatches = count($ids) > 0 ? $this->getMatchFindService()->adminGet(ids: $ids) : [];
        $allMatches = count($all_ids) > 0 ? $this->getMatchFindService()->adminGet(ids: $all_ids) : [];

        $returnObj = array(
            'all_matches' =>  $allMatches,
            'picked_matches' =>  $pickedMatches,
            'leagues' =>  $leagues
        );

        return $this->jsonResponse($response, 'success', $returnObj, 200);
    }
}
