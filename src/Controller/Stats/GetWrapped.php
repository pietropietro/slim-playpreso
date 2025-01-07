<?php

declare(strict_types=1);

namespace App\Controller\Stats;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetWrapped extends Base
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

        $wrapped = $this->getFindStatsService()->getWrapped($userId, 2024);
        $previous_year_wrapped = $this->getFindStatsService()->getWrapped($userId, 2023);
        
        return $this->jsonResponse($response, 'success', 
            array(
                'current'=>$wrapped, 
                'last'=>$previous_year_wrapped
            ),
            200
        );
    }
}
