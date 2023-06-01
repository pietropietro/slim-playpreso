<?php

declare(strict_types=1);

namespace App\Controller\UserParticipation;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);

        $activeAndPaused = $this->getParticipationService()
            ->getActiveAndPausedPPLeaguesForUser($userId);

        $notStarted = $this->getParticipationService()->getForUser($userId, null, started: false, finished: false);
        
        $ups = array(
            "active" => $activeAndPaused['active'],
            "waiting" => array_merge($activeAndPaused['paused'], $notStarted),
            "finished" => $this->getParticipationService()->getForUser(
                $userId, 
                'ppLeague', 
                started: null, 
                finished: true, 
                updatedAfter: '-1 month'
            ),
        );

        return $this->jsonResponse($response, 'success', $ups, 200);
    }
}