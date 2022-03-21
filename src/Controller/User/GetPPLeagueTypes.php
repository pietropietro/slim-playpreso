<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetPPLeagueTypes extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $input = (array) $request->getParsedBody();
        echo('input: <br><br>');
        print_r($input);

        $userId = $this->getAndValidateUserId($input);
        echo('userid: <br><br>');
        echo($userId);

        // $ppLeagueTypes = $this->getFindUserService()->getPPLeagueTypes((int) xx);

        return $this->jsonResponse($response, 'success', $user, 200);
    }
}
