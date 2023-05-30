<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

use App\Controller\BaseController;
use App\Service\PPTournamentType;
use App\Service\UserParticipation;

abstract class Base extends BaseController
{

    protected function getPPTournamentTypeService(): PPTournamentType\Find
    {
        return $this->container->get('pptournamenttype_find_service');
    }

    protected function getCheckPPTournamentService(): PPTournamentType\Check {
        return $this->container->get('pptournamenttype_check_service');
    }

    protected function getJoinPPTournamentTypeService(): PPTournamentType\Join {
        return $this->container->get('pptournamenttype_join_service');
    }

    protected function getUpdatePPTournamentService(): PPTournamentType\Update {
        return $this->container->get('pptournamenttype_update_service');
    }

    protected function getPPTournamentTypeCreateService(): PPTournamentType\Create {
        return $this->container->get('pptournamenttype_create_service');
    }

    protected function getUserParticipationFindService(): UserParticipation\Find {
        return $this->container->get('userparticipation_find_service');
    }


    

    //TODO DELETE?
    protected function checkUserPermissions(int $userId, int $userIdLogged): void
    {
        if ($userId !== $userIdLogged) {
            throw new User('User permission failed.', 400);
        }
    }
    

}
