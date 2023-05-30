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
        return $this->db->getValue( 'ppAreaLeagues' ,'country', null);
    }

    public function create(string $name){
        $data = array(
            "name" => $name, 
        );
        return $this->db->insert('ppAreas', $data);
    }

    public function update(int $id, array $data){
        $this->db->where('id', $id);
        return $this->db->update('ppAreas', $data, 1);        
    }
   
    public function removeCountry(int $ppAreaId, string $country){
        $this->db->where('ppArea_id', $ppAreaId);
        $this->db->where('country', $country);
        return $this->db->delete('ppAreaLeagues',1);
    }

    public function addCountry(int $ppAreaId, string $country){
        $data = array(
            "country" => $country,
            "ppArea_id" => $ppAreaId,
        );
        return $this->db->insert('ppAreaLeagues', $data);
    }

    public function removeLeague(int $ppAreaId, int $leagueId){
        $this->db->where('ppArea_id', $ppAreaId);
        $this->db->where('league_id', $leagueId);
        return $this->db->delete('ppAreaLeagues',1);
    }

    public function addLeague(int $ppAreaId, int $leagueId){
        $data = array(
            "league_id" => $leagueId,
            "ppArea_id" => $ppAreaId,
        );
        return $this->db->insert('ppAreaLeagues', $data);
    }

}