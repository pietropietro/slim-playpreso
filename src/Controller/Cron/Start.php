<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use Slim\Http\Request;
use Slim\Http\Response;

final class Start extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {
        $this->getGuessService()->setMissed();
        $leagues = $this->getLeaguesService()->getNeedData();

        foreach ($leagues as $key => $league) {
            if(!$league['ls_suffix'])continue;
            $this->getExternalApiService()->fetchExternalData($league['ls_suffix'], $league['id']);
        }

        return $this->jsonResponse($response, 'success', date('H:i:s T').',like: '.$leagues[0]['ls_suffix'], 200);
    }
}
