<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetNotVerified extends Base
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

        $guesses = $this->getFindGuessService()->notLocked($userId);
                 
        return $this->jsonResponse($response, "success", $guesses, 200);
    }
}
