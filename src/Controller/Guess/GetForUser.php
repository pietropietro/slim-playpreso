<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetForUser extends Base
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
        $page = (int) $request->getQueryParam('page', 2); // Default to page 1


        $guesses = $this->getGuessFindService()->getForUser(
            userId: $userId,
            includeMotd: true,
            locked: null,
            verified: true,
            page: $page,      
            limit: 20       
        );
                 
        return $this->jsonResponse($response, "success", $guesses, 200);
    }
}
