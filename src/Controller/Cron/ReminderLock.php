<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Service\Mailer;

final class ReminderLock extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {

        $needReminder = $this->getEmailPreferencesFindService()->getNeedLockReminder();

        foreach ($needReminder as $value) {
            $matchesIds = explode(',', $value['matches_id_concat']);
            $prepared = $this->getEmailBuilderLockService()->prepare($value['username'], $matchesIds);
            Mailer::send(array($value['email']), $prepared['subject'], $prepared['contentHtml'], $emailerror);
        }
        
        return $this->jsonResponse($response, 'success', 'sent', 200);
    }
}
