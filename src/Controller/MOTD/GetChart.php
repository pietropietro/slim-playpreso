<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetChart extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $page = (int) $request->getQueryParam('page', 1); // Default to page 1
        $limit = (int) $request->getQueryParam('limit', null); // Default limit to 50

        $result = $this->getMotdLeaderService()->getChart($page, $limit);
        if(!$result){
            throw new \App\Exception\NotFound('chart Not Found.', 404);
        }

        foreach ($result['chart'] as &$item) {
            $item['user'] = $this->getUserFindService()->getOne($item['user_id']);
        }

        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
