<?php

declare(strict_types=1);

namespace App\Controller\PPArea;

use App\Controller\BaseController;
use App\Service\PPArea;

abstract class Base extends BaseController
{

    protected function getPPAreaFindService(): PPArea\Find
    {
        return $this->container->get('pparea_find_service');
    }

    protected function getPPAreaUpdateService(): PPArea\Update {
        return $this->container->get('pparea_update_service');
    }

    protected function getPPAreaCreateService(): PPArea\Create {
        return $this->container->get('pparea_create_service');
    }    

}
