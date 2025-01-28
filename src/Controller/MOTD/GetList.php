<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetList extends Base
{
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        // 1) Identify the user (from JWT or session, etc.)
        $userId = $this->getAndValidateUserId($request);

        $page = (int) $request->getQueryParam('page', 1); // Default to page 1
        $limit = (int) $request->getQueryParam('limit', 10); // Default limit to 10

        $list = $this->getMotdFindService()->get(verified: true, page: $page, limit: $limit);

        //add extra data 
        foreach ($list as $key => $val) {
            $list[$key] = $this->prepareUserMotdItem($val, $userId);
        }

        $returnArray = [
            'list' => $list,
        ];

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }

}
