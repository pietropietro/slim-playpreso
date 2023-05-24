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

    public function addTournament(int $ppAreaId, int $tournamentId){
        $this->ppAreaRepository->addTournament($ppAreaId, $tournamentId);
     }
 
     public function removeTournament(int $ppAreaId, int $tournamentId){
         $this->ppAreaRepository->removeTournament($ppAreaId, $tournamentId);
     }

}
