<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetUserLastNext extends Base
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

        $returnArray = array(
            "next" => $this->getFindGuessService()->getNext($userId),
            "last" => $this->getFindGuessService()->getLast($userId, '-1 week')
        );
                 
        return $this->jsonResponse($response, "success", $returnArray, 200);
    }
}
