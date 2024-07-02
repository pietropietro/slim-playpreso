<?php

declare(strict_types=1);

namespace App\Controller\Stats;

use Slim\Http\Request;
use Slim\Http\Response;

final class User extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $userId = (int) $args['id'];

        // Get current date and date 30 days ago
        $to = (new \DateTime())->format('Y-m-d');
        $from = (new \DateTime('-30 days'))->format('Y-m-d');

        // Get stats for the last 30 days
        $statsMonth = $this->getStatsUserService()->getForUser($userId, $from, $to);

        // Get all-time stats
        $statsAllTime = $this->getStatsUserService()->getForUser($userId);

        $stats = [
            'lastMonth' => $statsMonth,
            'allTime' => $statsAllTime,
        ];



        return $this->jsonResponse($response, 'success', $stats, 200);
    }
}
