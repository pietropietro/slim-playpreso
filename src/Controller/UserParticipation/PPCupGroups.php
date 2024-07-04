<?php

declare(strict_types=1);

namespace App\Controller\UserParticipation;

use Slim\Http\Request;
use Slim\Http\Response;

final class PPCupGroups extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        // $userId = $this->getAndValidateUserId($request);
        $userId = (int) $args['id'];

        $ups = array();

        $active = $this->getParticipationService()->getForUser($userId, 'ppCupGroup',  started: null, finished: false);
        $finished = $this->getParticipationService()->getForUser(
            $userId, 
            'ppCupGroup', 
            started: null, 
            finished: true, 
            updatedAfter: '-1 month'
        );

        //1.remove active ppCup_id from  the finished array
        // Extract ppCup_id values from active array
        $activeIds = array_column($active, 'ppCup_id');

        // Filter the finished array
        $filteredFinished = array_filter($finished, function ($element) use ($activeIds) {
            return !in_array($element['ppCup_id'], $activeIds);
        });

        //2. only keep the most recently finished group of same ppCup.
        // Initialize an array to hold the latest ppCupGroups for same ppCup_id
        $latestFinished = [];

        foreach ($finished as $item) {
            $id = $item['ppCup_id'];
            $date = $item['joined_at'];

            // Check if ppCup_id is not in the active array
            if (!in_array($id, $activeIds)) {
                // Check if this item is later than the existing one
                if (!isset($latestFinished[$id]) || $date > $latestFinished[$id]['joined_at']) {
                    $latestFinished[$id] = $item;
                }
            }
        }

        // Convert back to indexed array
        $latestFinished = array_values($latestFinished);

        if($active) $ups['active'] = $active;
        if($latestFinished) $ups['finished'] = $latestFinished;

        return $this->jsonResponse($response, 'success', $ups, 200);
    }
}