<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPAreaRepository extends BaseRepository
{
    public function get() {
        return $this->db->get('ppAreas');
    }

    function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('ppAreas');
    }

    function getCountries(int $id){
        $this->db->where('ppArea_id', $id);
        $this->db->where('country is not null');
        return $this->db->getValue( 'ppAreaTournaments' ,'country', null);
    }

    public function update(int $id, array $data){
        $this->db->where('id', $id);
        return $this->db->update('ppAreas', $data, 1);        
    }
   
    public function removeCountry(int $ppAreaId, string $country){
        $this->db->where('ppArea_id', $ppAreaId);
        $this->db->where('country', $country);
        return $this->db->delete('ppAreaTournaments',1);
    }

    public function addCountry(int $ppAreaId, string $country){
        $data = array(
            "country" => $country,
            "ppArea_id" => $ppAreaId,
        );
        return $this->db->insert('ppAreaTournaments', $data);
    }

    public function removeTournament(int $ppAreaId, int $tournamentId){
        $this->db->where('ppArea_id', $ppAreaId);
        $this->db->where('tournament_id', $tournamentId);
        return $this->db->delete('ppAreaTournaments',1);
    }

    public function addTournament(int $ppAreaId, int $tournamentId){
        $data = array(
            "tournament_id" => $tournamentId,
            "ppArea_id" => $ppAreaId,
        );
        return $this->db->insert('ppAreaTournaments', $data);
    }

}