<?php

declare(strict_types=1);

namespace App\Controller\Highlight;

use App\Controller\BaseController;
use App\Service\Highlight;
use App\Service\Trophy;
use App\Service\User;
use App\Service\Guess;
use App\Service\PPRound;


abstract class Base extends BaseController
{
    protected function getHighlightFindService(): Highlight\Find
    {
        return $this->container->get('highlight_find_service');
    }

    protected function getTrophyFindService(): Trophy\Find
    {
        return $this->container->get('trophy_find_service');
    }


    protected function getUserFindService(): User\Find
    {
        return $this->container->get('user_find_service');
    }


    protected function getGuessFindService(): Guess\Find
    {
        return $this->container->get('guess_find_service');
    }

    protected function getPPRoundFindService(): PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

    


}
