<?php

declare(strict_types=1);

namespace App\Service\PPArea;

use App\Service\BaseService;
use App\Repository\PPAreaRepository;

final class Create extends BaseService{
    public function __construct(
        protected PPAreaRepository $ppAreaRepository,
    ) {}
    
    public function create(
        string $name
    ){
        return $this->ppAreaRepository->create(
            $name, 
        );
    }

}