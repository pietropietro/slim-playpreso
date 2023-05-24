<?php

declare(strict_types=1);

namespace App\Controller\PPArea;

use Slim\Http\Request;
use Slim\Http\Response;

final class RemoveCountry extends Base
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

        $ppAreaId = (int) $args['id'];
        $country = (string) $args['country'];
        $result = $this->getPPAreaUpdateService()->removeCountry($ppAreaId, $country);
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
