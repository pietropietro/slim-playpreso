<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use App\Controller\BaseController;
use App\Service\ExternalAPI;
use App\Service\League;
use App\Service\PPLeague;
use App\Service\PPRound;
use App\Service\Guess;
use App\Service\PPRoundMatch;
use App\Service\MOTD;
use App\Service\Match;
use App\Service\Team;
use App\Service\EmailPreferences;
use App\Service\EmailBuilder;

abstract class Base extends BaseController
{
    protected function getImportLeagueDataService(): ExternalAPI\ImportLeagueData
    {
        return $this->container->get('external_api_importleaguedata_service');
    }

    protected function getLeaguesService(): League\Find
    {
        return $this->container->get('league_find_service');
    }

    protected function getPPLeaguesFindService(): PPLeague\Find
    {
        return $this->container->get('ppleague_find_service');
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

    protected function getMotdFindService(): MOTD\Find
    {
        return $this->container->get('motd_find_service');
    }

    protected function getMatchPickerService(): Match\Picker
    {
        return $this->container->get('match_picker_service');
    }

    protected function getMotdCreateService(): MOTD\Create
    {
        return $this->container->get('motd_create_service');
    }

    protected function getTeamFindService(): Team\Find
    {
        return $this->container->get('team_find_service');
    }

    protected function getImportTeamLogoService(): ExternalAPI\ImportTeamLogo
    {
        return $this->container->get('external_api_importteamlogo_service');
    }

    protected function getPPRoundFindService(): PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

    protected function getPPRoundCreateService(): PPRound\Create
    {
        return $this->container->get('ppround_create_service');
    }

}
