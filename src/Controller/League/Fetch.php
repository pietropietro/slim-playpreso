<?php

declare(strict_types=1);

namespace App\Controller\League;

use Slim\Http\Request;
use Slim\Http\Response;

final class Fetch extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        $leagueId = (int) $args['id'];
        $league = $this->getFindLeagueService()->getOne($leagueId);
        
        if ($league['use_match_ls_suffix'] && !isset($data->match_ls_suffix)) {
            throw new \App\Exception\User('missing required fields', 400);
        }
       
        $result = $this->getExternalApiService()->fetchExternalData(
            !!$league['use_match_ls_suffix'] ? $data->match_ls_suffix : $league['ls_suffix'], 
            $league['id'], 
            !!$league['use_match_ls_suffix']
        );
        
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
