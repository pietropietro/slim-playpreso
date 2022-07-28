<?php

declare(strict_types=1);

namespace App\Controller\PPCupGroup;

use App\Controller\BaseController;
use App\Service;


abstract class Base extends BaseController
{
    protected function getCupGroupService(): Service\PPCupGroup\Find
    {
        return $this->container->get('ppcupgroup_service');
    } 

    protected function getUserParticipationService(): Service\UserParticipation\Find
    {
        return $this->container->get('user_participation_service');
    }    

    protected function getCupTypeService(): Service\PPCupType\Find
    {
        return $this->container->get('ppcuptype_service');
    }  
    protected function getFindCupService(): Service\PPCup\Find
    {
        return $this->container->get('ppcup_find_service');
    }  
    protected function getPPRoundService(): Service\PPRound\Find
    {
        return $this->container->get('ppround_service');
    }

}
