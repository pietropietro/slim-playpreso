<?php

declare(strict_types=1);

namespace App\Service\EmailBuilder;

use App\Service\Match;
use App\Service\BaseService;

final class LockReminder extends BaseService
{
    public function __construct(
        protected Match\Find $matchFindService,
    ) {}

    public function prepare(string $username, array $matchesIds){
        $matches = $this->matchFindService->get($matchesIds);

        $subject = 'LOCK TODAY: ';
        $subject .= $matches[0]['homeTeam']['name'].' - '.$matches[0]['awayTeam']['name'];
        $subject .= count($matches) > 1 ? (', and '.(count($matches) - 1).' more') : '';
        $html = 'hi '.$username.', <a href="https://www.playpreso.com">click here to lock</a>';

        foreach ($matches as $value) {
            $html .= '<div class="margin-bottom:10px;">';
            $html .= '<h1>'.$value["homeTeam"]["name"].' - '.$value["awayTeam"]["name"].'</h1>';
            $html .= '<p>'.$value["league"]["name"].'</p>';
            $html .= '<p>'.$value["date_start"].'</p>';
            $html .= '</div>';
        }

        $html .= '<i>you can stop receiving this reminder by changing your preferences in playpreso.</i>';
        return array (
            'subject' => $subject,
            'contentHtml' => $html
        );
    }

}

