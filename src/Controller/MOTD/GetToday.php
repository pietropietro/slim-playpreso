<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetToday extends Base
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
        
        $yesterday = (new \DateTime('yesterday'))->format('Y-m-d');
        $motdToday    = $this->getMotdFindService()->getMotd(null, withGuesses:true);
        $motdYesterday = $this->getMotdFindService()->getMotd($yesterday, withGuesses:true);

        //For each match, prepare the "guess" data for the user
        $motdToday    = $this->prepareUserMotdItem($motdToday, $userId);
        $motdYesterday = $this->prepareUserMotdItem($motdYesterday, $userId);
        
        $returnArray = array(
            "today" => $motdToday, 
            "yesterday" => $motdYesterday, 
        );

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }
}
