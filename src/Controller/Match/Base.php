<?php

declare(strict_types=1);

namespace App\Controller\Match;

use App\Controller\BaseController;
use App\Service\Match;
use App\Service\League;


abstract class Base extends BaseController
{
    protected function getFindMatchService(): Match\Find
    {
        return $this->container->get('match_find_service');
    }
    
    protected function getVerifyMatchService(): Match\Verify
    {
        return $this->container->get('match_verify_service');
    }

    protected function getPickMatchService(): Match\Picker
    {
        return $this->container->get('match_picker_service');
    }

    protected function getFindLeagueService(): League\Find
    {
        return $this->container->get('league_find_service');
    }
    

}
