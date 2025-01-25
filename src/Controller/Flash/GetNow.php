<?php

declare(strict_types=1);

namespace App\Controller\Flash;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * GET /flash
 *
 * Returns a JSON structure with:
 *   - flash_next (the soonest upcoming)
 *   - flash_current (the one in progress if any)
 *   - flash_last (the most recently finished)
 */
final class GetNow extends Base
{
    /**
     * Handle the route: GET /flash
     *
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        // 1) Identify the user (from JWT or session, etc.)
        $userId = $this->getAndValidateUserId($request);

        // 2) Retrieve next/current/last flash matches
        //    (Implement these queries in your Flash\Find service)
        $flashNext    = $this->getFlashFindService()->getNextFlash($userId);
        $flashCurrent = $this->getFlashFindService()->getCurrentFlash($userId);
        $flashLast    = $this->getFlashFindService()->getLastFlash(dateString: null, verified: true, userId: $userId);

        // 3) For each match, prepare the "guess" data for the user
        $flashNext    = $this->prepareUserFlashItem($flashNext, $userId);
        $flashCurrent = $this->prepareUserFlashItem($flashCurrent, $userId);
        $flashLast    = $this->prepareUserFlashItem($flashLast, $userId);

        // 4) Return all three in a single response
        $returnArray = [
            'next'    => $flashNext,
            'current' => $flashCurrent,
            'last'    => $flashLast,
        ];

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }

    
}
