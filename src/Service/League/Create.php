<?php

declare(strict_types=1);

namespace App\Service\League;

use App\Service\BaseService;
use App\Repository\LeagueRepository;

final class Create extends BaseService{
    public function __construct(
        protected LeagueRepository $leagueRepository,
    ) {}
    
    public function create(
        string $name, 
        string $tag, 
        string $country, 
        int $country_level, 
        string $area, 
        int $area_level, 
        ?int $parent_id = null,
        ?string $ls_suffix = null,
    ){
        return $this->leagueRepository->create(
            $name, 
            $tag,
            $country,
            $country_level,
            $area,
            $area_level,            
            $parent_id,
            $ls_suffix
        );
    }

}