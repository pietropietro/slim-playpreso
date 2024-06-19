<?php

declare(strict_types=1);

namespace App\Controller\PPRanking;

use Slim\Http\Request;
use Slim\Http\Response;

final class Get extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {

        $userId = $this->getAndValidateUserId($request);

        $page = (int) $request->getQueryParam('page', 1); // Default to page 1
        $limit = (int) $request->getQueryParam('limit', null); // Default limit to 50

        // $date = date('Y-m-d'); // Assuming you want today's rankings.
        $result = $this->getPPRankingFindService()->getRankingsForDate(null, $page ,$limit);
        foreach ($result['ppRankings'] as &$ranking) {
            $ranking['user'] = $this->getUserFindService()->getOne($ranking['user_id']);
        }
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
