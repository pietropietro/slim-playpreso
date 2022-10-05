<?php

declare(strict_types=1);

namespace App\Controller\PPCup;

use App\Controller\BaseController;
use App\Service;


abstract class Base extends BaseController
{
    protected function getCupCountService(): Service\PPCup\Count
    {
        return $this->container->get('ppcup_count_service');
    } 
    protected function getFindCupService(): Service\PPCup\Find
    {
        return $this->container->get('ppcup_find_service');
    } 
    protected function getCreateCupService(): Service\PPCup\Create
    {
        return $this->container->get('ppcup_create_service');
    } 
    protected function getTournamentTypeService(): Service\PPTournamentType\Find
    {
        return $this->container->get('pptournamenttype_find_service');
    }    
}
