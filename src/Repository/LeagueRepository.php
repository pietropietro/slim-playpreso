<?php

declare(strict_types=1);

namespace App\Repository;

final class LeagueRepository extends BaseRepository
{
    public function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('leagues');
    }

    public function getForArea(string $area, int $level)
    {
        $this->db->where('area', $area);

        switch ($level) {
			case 1:
			case 2:
			case 3:
                $this->db->where('area_level',$level);
                $this->db->where('country_level',1);
				break;
			case 4:
                $this->db->where('country_level',2);
				break;
		}

        return $this->db->get('leagues');
    }

    public function getUefa(){
		$this->db->where('country','AAA');
		return $this->db->get('leagues');
    }

    public function getForCountry(?string $country, int $level, bool $id_only = false){
        if($country){
            $this->db->where('country',$country);
        }
        $this->db->where('country_level', $level, '<=');
        if($id_only){
            return $this->db->getValue('leagues', 'id', null);
        }
        return $this->db->get('leagues');
    }

    public function updateStandings(int $id, string $standings_json){
        $data = array(
            "standings" => $standings_json,
            "updated_at" => $this->db->now()
        );

        $this->db->where('id', $id);
        $this->db->update('leagues', $data, 1);        
    }

    public function getNeedData(){
        $this->db->join("matches m", "m.league_id=l.id", "INNER");
        $this->db->where('m.verified_at IS NULL');
        $start = date("Y-m-d H:i:s", strtotime('-3 days'));
        $finish = date("Y-m-d H:i:s");
        $this->db->where('m.date_start', array($start, $finish), 'BETWEEN');
        return $this->db->query("select distinct CONCAT(l.ls_suffix, m.ls_suffix) as ls_suffix, l.id, from leagues l");
    }

}