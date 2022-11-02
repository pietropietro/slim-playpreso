<?php

declare(strict_types=1);

namespace App\Service\PPCupGroup;

use App\Repository\PPCupGroupRepository;
use App\Service\BaseService;

final class Update  extends BaseService{
    public function __construct(
        protected PPCupGroupRepository $ppCupGroupRepository,
    ) {}

    public function setFinished(int $id){
        $this->ppCupGroupRepository->setFinished($id);
        //TODO HANDLE GROUP PROMOTION
        
    }

    

}
