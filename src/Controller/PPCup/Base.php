<?php

declare(strict_types=1);

namespace App\Controller\PPCup;

use App\Controller\BaseController;
use App\Service\PPCup;
use App\Service\PPTournamentType;
use App\Service\User;


abstract class Base extends BaseController
{
    protected function getCupCountService(): PPCup\Count
    {
        return $this->container->get('ppcup_count_service');
    } 
    protected function getFindCupService(): PPCup\Find
    {
        return $this->container->get('ppcup_find_service');
    } 
    protected function getCreateCupService(): PPCup\Create
    {
        return $this->container->get('ppcup_create_service');
    } 
    protected function getTournamentTypeService(): PPTournamentType\Find
    {
        return $this->container->get('pptournamenttype_find_service');
    }    

    protected function getUserFindService(): User\Find
    {
        return $this->container->get('user_find_service');
    }   
}

