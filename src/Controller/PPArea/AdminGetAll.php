<?php

declare(strict_types=1);

namespace App\Controller\PPArea;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetAll extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        
        $ppAreas = $this->getPPAreaFindService()->get();
        return $this->jsonResponse($response, 'success', $ppAreas, 200);
    }
}
