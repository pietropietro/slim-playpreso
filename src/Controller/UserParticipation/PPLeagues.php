<?php

declare(strict_types=1);

namespace App\Controller\UserParticipation;

use Slim\Http\Request;
use Slim\Http\Response;

final class PPLeagues extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);
        $ups = array();

        $activeAndPaused = $this->getParticipationService()
            ->getActiveAndPausedPPLeaguesForUser($userId);

        $notStarted = $this->getParticipationService()->getForUser($userId, 'ppLeague', started: false, finished: false);
        
        $active = $activeAndPaused['active'];
        $waiting = array_merge($activeAndPaused['paused'], $notStarted);
        $finished = $this->getParticipationService()->getForUser(
            $userId, 
            'ppLeague', 
            started: null, 
            finished: true, 
            updatedAfter: '-1 month'
        );

        if($active) $ups['active'] = $active;
        if($waiting) $ups['waiting'] = $waiting;
        if($finished) $ups['finished'] = $finished;

        return $this->jsonResponse($response, 'success', $ups, 200);
    }
}