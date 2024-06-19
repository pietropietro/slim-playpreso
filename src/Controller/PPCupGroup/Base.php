<?php

declare(strict_types=1);

namespace App\Controller\PPCupGroup;

use App\Controller\BaseController;
use App\Service\PPCupGroup;
use App\Service\User;
use App\Service\UserParticipation;
use App\Service\PPRound;
use App\Service\PPCup;
use App\Service\PPTournamentType;


abstract class Base extends BaseController
{
    protected function getCupGroupService(): PPCupGroup\Find
    {
        return $this->container->get('ppcupgroup_find_service');
    } 

    protected function getUserParticipationService(): UserParticipation\Find
    {
        return $this->container->get('userparticipation_find_service');
    }    

    protected function getTournamentTypeService(): PPTournamentType\Find
    {
        return $this->container->get('pptournamenttype_find_service');
    }  
    protected function getFindCupService(): PPCup\Find
    {
        return $this->container->get('ppcup_find_service');
    }  
    protected function getPPRoundService(): PPRound\Find
    {
        return $this->container->get('ppround_find_service');
    }

    protected function getUserFindService(): User\Find
    {
        return $this->container->get('user_find_service');
    }

}
