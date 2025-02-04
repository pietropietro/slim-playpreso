<?php

declare(strict_types=1);

namespace App\Controller\Stats;

use Slim\Http\Request;
use Slim\Http\Response;

final class MainInfo extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $userId = $this->getAndValidateUserId($request);

        // Get current date and date 30 days ago
        $to = (new \DateTime())->format('Y-m-d');
        $from = (new \DateTime('-30 days'))->format('Y-m-d');

        // Get stats for the last 30 days
        $mainSummary = $this->getStatsUserService()->getUserMainSummary($userId, $from, $to);
        $ranking = $this->getPPRankingFindService()->getForUser($userId);
        $trophies = $this->getTrophyFindService()->getTrophies($userId);
        $inactive = $this->getUserRepository()->isInactive($userId);

        $unredNotificationCount = $this->getUserNotificationFindService()->countUnread($userId);


        $returnArray = [
            'avg' => $mainSummary['avg_points'],
            'ppRanking' => $ranking['position'],
            'trophies' => $trophies,
            'inactive' => $inactive,
            'unreadNotificationCount' => $unredNotificationCount
        ];




        return $this->jsonResponse($response, 'success', $returnArray , 200);
    }
}
