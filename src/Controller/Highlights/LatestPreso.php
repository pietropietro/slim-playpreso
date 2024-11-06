<?php

declare(strict_types=1);

namespace App\Controller\Highlights;

use Slim\Http\Request;
use Slim\Http\Response;

final class LatestPreso extends Base
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
        $limit = 11; 
       
        $presosSummaries = $this->getHighlightsPresosService()->getLastPresos($page, $limit);

        $arr=array("preso" => $presosSummaries,);

        return $this->jsonResponse($response, 'success', $arr, 200);
    }
}
