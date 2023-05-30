<?php

declare(strict_types=1);

namespace App\Service\PPArea;

use App\Repository\PPAreaRepository;
use App\Service\BaseService;

final class Update  extends BaseService{
    public function __construct(
        protected PPAreaRepository $ppAreaRepository,
    ) {}

    public function update(int $ppAreaId, array $data){
        return $this->ppAreaRepository->update($ppAreaId, $data);
    }

    public function addCountry(int $ppAreaId, string $country){
       $this->ppAreaRepository->addCountry($ppAreaId, $country);
    }

    public function removeCountry(int $ppAreaId, string $country){
        $this->ppAreaRepository->removeCountry($ppAreaId, $country);
    }

    public function addLeague(int $ppAreaId, int $leagueId){
        $this->ppAreaRepository->addLeague($ppAreaId, $leagueId);
     }
 
     public function removeLeague(int $ppAreaId, int $leagueId){
         $this->ppAreaRepository->removeLeague($ppAreaId, $leagueId);
     }

}
