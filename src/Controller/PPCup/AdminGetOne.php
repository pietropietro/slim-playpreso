<?php

declare(strict_types=1);

namespace App\Controller\PPCup;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetOne extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $is_slug = !is_numeric($args['id']);
        $ppCupId = $is_slug ? $args['id'] : (int) $args['id'];

        $ppCup = $this->getFindCupService()->getOne(
            $ppCupId, 
            $is_slug, 
            null
        );
                
        return $this->jsonResponse($response, 'success', $ppCup, 200);
    }
}
