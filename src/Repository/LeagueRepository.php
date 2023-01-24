<?php

declare(strict_types=1);

namespace App\Repository;

final class LeagueRepository extends BaseRepository
{
    private $columnsNoStandings = "id, name, tag, country, area, ls_suffix, parent_id, area_level, country_level";
    private $columnsWithStandings = "id, name, tag, country, area, ls_suffix, parent_id, standings";

    public function get(){
        return $this->db->get('leagues');
    }

    public function getOne(int $id, ?bool $withStandings = false){
        $this->db->where('id', $id);
        return $this->db->getOne(
            'leagues', 
            $withStandings ? $this->columnsWithStandings : $this->columnsNoStandings
        );
    }

    public function getForArea(string $area, ?int $level = null, ?bool $id_only = false)
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
        if($id_only){
            return $this->db->getValue('leagues', 'id', null);
        }
        return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function getUefa(){
		$this->db->where('country','Europe');
		return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function getForCountry(?string $country, int $level, bool $id_only = false){
        if($country){
            $this->db->where('country',$country);
        }
        $this->db->where('country_level', $level, '<=');
        if($id_only){
            return $this->db->getValue('leagues', 'id', null);
        }
        return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function update(int $id, array $data){
        $this->db->where('id', $id);
        $this->db->update('leagues', $data, 1);        
    }

    public function create(string $name,?int $parentId) : int {
        $data = array(
            "name" => $name,
            "parent_id" => $parentId,
            "created_at" => $this->db->now()
        );
        return $this->db->insert('leagues', $data);
    }

    public function updateStandings(int $id, string $standings_json){
        $data = array(
            "standings" => $standings_json,
            "updated_at" => $this->db->now()
        );

        $this->db->where('id', $id);
        $this->db->update('leagues', $data, 1);        
    }

    public function getNeedData(bool $havingGuesses = true, ?string $fromTime = null){
        $this->db->join("matches m", "m.league_id=l.id", "INNER");
        if($havingGuesses)$this->db->join("guesses g", "g.match_id=m.id", "INNER");
        $this->db->where('m.verified_at IS NULL');
        $this->db->where('m.notes IS NULL');
        $start = date("Y-m-d H:i:s", strtotime($fromTime ?? '-400 min'));
        $finish = date("Y-m-d H:i:s", strtotime('-110 minutes'));
        $this->db->where('m.date_start', array($start, $finish), 'BETWEEN');
        return $this->db->query("select distinct ls_suffix, l.id, l.tag, l.country from leagues l");
    }

}