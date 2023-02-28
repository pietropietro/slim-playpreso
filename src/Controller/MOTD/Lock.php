<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class Lock extends Base
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

        if (!isset($data->home) || !isset($data->away)) {
            throw new \App\Exception\User('missing required fields', 400);
        }

        $userId = $this->getAndValidateUserId($request);
        
        $motdPPRM = $this->getPPRoundMatchFindService()->getMotd($userId);
        if(!$motdPPRM || $motdPPRM['guess']){
            throw new \App\Exception\NotFound("can't lock", 400);
        }

        $newGuessId = $this->getGuessCreateService()->create($userId, $motdPPRM['match_id'], $motdPPRM['id']);
        if(!$newGuessId){
            throw new \App\Exception\NotFound("can't lock", 400);
        }

        $this->getGuessLockService()->lock($newGuessId, $userId, $data->home, $data->away);
                 
        return $this->jsonResponse($response, "success", $newGuessId, 200);
    }
}
