<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use Slim\Http\Request;
use Slim\Http\Response;

final class PickMotd extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {
        
        if($this->getPPRoundMatchFindService()->getMotd()){
            return $this->jsonResponse($response, 'fail', 'already picked motd', 403);
        }

        $match = $this->getMatchPickerService()->pickForToday();

        if(!$match){
            return $this->jsonResponse($response, 'fail', 'no match', 404);
        };

        $newPPRMid = $this->getPPRoundMatchCreateService()->create($match['id']);
        return $this->jsonResponse($response, 'success', $newPPRMid, 200);
    }
}
