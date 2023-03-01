<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminSet extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $matchId = (int) $args['matchId'];        
        $match = $this->getMatchFindService()->getOne($matchId);
        if(!$match || $match['verified_at'] || !$this->getMatchFindService()->isBeforeStartTime($matchId)){
            throw new \App\Exception\NotFound("can't use this match", 400);
        }

        $matchDate = new \DateTime($match['date_start']);

        $oldMotd = $this->getPPRoundMatchFindService()->getMotd($matchDate->format('Y-m-d'));
        if($oldMotd){
            $this->getDeletePPRoundMatchService()->delete($oldMotd['id']);
        }

        $newMotdId = $this->getPPRoundMatchCreateService()->create($match['id']);

        return $this->jsonResponse($response, "success", $newMotdId, 200);
    }
}
