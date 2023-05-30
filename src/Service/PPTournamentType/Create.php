<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\BaseService;
use App\Repository\PPTournamentTypeRepository;

final class Create extends BaseService{
    public function __construct(
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
    ) {}
    
    public function create(
        string $name, 
        int $cost, 
        string $rgb,
        string $emoji,
        ?int $level = null, 
        ?int $rounds = null, 
        ?int $participants = null,
        ?string $pick_country = null,
        ?int $pick_area = null,
        ?int $pick_league = null,
    ){
        return $this->ppTournamentTypeRepository->create(
            $name, 
            $cost, 
            $rgb,
            $emoji,
            $level, 
            $rounds, 
            $participants,
            $pick_country,
            $pick_area,
            $pick_league,
        );
    }

}