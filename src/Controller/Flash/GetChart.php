<?php

declare(strict_types=1);

namespace App\Controller\Flash;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetChart extends Base
{
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);

        $page = (int) $request->getQueryParam('page', 1); // Default to page 1
        $limit = (int) $request->getQueryParam('limit', 10); // Default limit to 10

        $result = $this->getFlashFindService()->getChart(page: $page, limit: $limit);

        foreach ($result['chart'] as &$item) {
            $item['user'] = $this->getUserFindService()->getOne($item['user_id']);
        }

        $returnArray = [
            'chart' => $result['chart']
        ];

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }

}
