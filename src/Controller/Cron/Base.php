<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use App\Controller\BaseController;
use App\Service\ExternalAPI\Call;
use App\Service\League;
use App\Service\Guess;
use App\Service\PPRoundMatch;
use App\Service\Match;
use App\Service\EmailPreferences;
use App\Service\EmailBuilder;

abstract class Base extends BaseController
{
    protected function getExternalApiService(): Call
    {
        return $this->container->get('external_api_service');
    }

    protected function getLeaguesService(): League\Find
    {
        return $this->container->get('league_find_service');
    }

    protected function getGuessVerifyService(): Guess\Verify
    {
        return $this->container->get('guess_verify_service');
    }

    protected function getGuessFindService(): Guess\Find
    {
        return $this->container->get('guess_find_service');
    }

    protected function getEmailPreferencesFindService(): EmailPreferences\Find
    {
        return $this->container->get('emailpreferences_find_service');
    }

    protected function getEmailBuilderLockService(): EmailBuilder\LockReminder
    {
        return $this->container->get('emailbuilder_lockreminder_service');
    }

    protected function getPPRoundMatchFindService(): PPRoundMatch\Find
    {
        return $this->container->get('pproundmatch_find_service');
    }

    protected function getMatchPickerService(): Match\Picker
    {
        return $this->container->get('match_picker_service');
    }

    protected function getPPRoundMatchCreateService(): PPRoundMatch\Create
    {
        return $this->container->get('pproundmatch_create_service');
    }

}
