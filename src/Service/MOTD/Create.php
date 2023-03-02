<?php

declare(strict_types=1);

namespace App\Service\MOTD;

use App\Service\BaseService;
use App\Repository\MOTDRepository;

final class Create  extends BaseService{
    public function __construct(
        protected MOTDRepository $motdRepository,
    ){}
    
    public function create(
        int $matchId, 
    ) : int {

        if(!$id = $this->motdRepository->create($matchId)){
            throw new \App\Exception\Mysql("could not create motd", 500);
        }

        return $id;
    }
    
}
