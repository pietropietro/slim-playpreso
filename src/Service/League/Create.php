<?php

declare(strict_types=1);

namespace App\Service\League;

use App\Service\BaseService;
use App\Repository\LeagueRepository;

final class Create extends BaseService{
    public function __construct(
        protected LeagueRepository $leagueRepository,
    ) {}
    
    public function create(string $name, ?int $parentId){
        return $this->leagueRepository->create($name, $parentId);
    }

}