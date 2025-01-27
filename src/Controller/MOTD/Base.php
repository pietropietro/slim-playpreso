<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use App\Controller\BaseController;
use App\Service\PPRoundMatch;
use App\Service\Guess;
use App\Service\Match;
use App\Service\MOTD;
use App\Service\PPTournamentType;
use App\Service\User;


abstract class Base extends BaseController
{
    protected function getMotdFindService(): MOTD\Find
    {
        return $this->container->get('motd_find_service');
    } 

    protected function getDeletePPRoundMatchService(): PPRoundMatch\Delete
    {
        return $this->container->get('pproundmatch_delete_service');
    }

    protected function getPPRoundMatchFindService(): PPRoundMatch\Find
    {
        return $this->container->get('pproundmatch_find_service');
    }

    protected function getMotdCreateService(): MOTD\Create
    {
        return $this->container->get('motd_create_service');
    }

    protected function getMatchFindService(): Match\Find
    {
        return $this->container->get('match_find_service');
    } 

    protected function getGuessCreateService(): Guess\Create
    {
        return $this->container->get('guess_create_service');
    }

    protected function getGuessLockService(): Guess\Lock
    {
        return $this->container->get('guess_lock_service');
    }

    protected function getPPTournamentTypeFindService(): PPTournamentType\Find
    {
        return $this->container->get('pptournamenttype_find_service');
    }

    protected function getMotdLeaderService(): MOTD\Leader
    {
        return $this->container->get('motd_leader_service');
    }

    protected function getUserFindService(): User\Find
    {
        return $this->container->get('user_find_service');
    }


     /**
     * Helper method: If there's a Flash match row, attach a "dummy" guess,
     */
    protected function prepareUserMotdItem(?array $pprmMotd, int $userId): ?array
    {
        if (!$pprmMotd) {
            return null; // No match found, return null
        }

        // Filter guesses for the same userId from the provided `pprmMotd` array
        $userGuess = array_filter($pprmMotd['guesses'] ?? [], function ($guess) use ($userId) {
            return $guess['user_id'] === $userId;
        });

        // If no guess exists for the user, build a dummy guess object
        if (empty($userGuess)) {
            $pprmMotd['guess'] = $this->getGuessCreateService()->buildDummyGuess($userId, $pprmMotd['id'], 'motd');
        } else {
            $pprmMotd['guess'] = reset($userGuess); // Use the first match
        }

        // Add the PPTournamentType to the guess
        $motdPPtt = $this->getPPTournamentTypeFindService()->getMOTDType();
        $pprmMotd['guess']['ppTournamentType'] = $motdPPtt;

        return $pprmMotd;
    }

    

}
