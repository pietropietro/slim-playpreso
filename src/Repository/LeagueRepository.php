<?php

declare(strict_types=1);

namespace App\Repository;

final class LeagueRepository extends BaseRepository
{
    private $columnsNoStandings = "id, name, tag, country, ls_suffix, parent_id, level";
    private $columnsWithStandings = "id, name, tag, country, ls_suffix, parent_id, standings";

    public function get(?int $maxLevel=null){
        if($maxLevel) $this->db->where('level', $maxLevel, '<=');
        return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function getOne(int $id, ?bool $withStandings = false){
        $this->db->where('id', $id);
        return $this->db->getOne(
            'leagues', 
            $withStandings ? $this->columnsWithStandings : $this->columnsNoStandings
        );
    }

    public function getForArea(int $ppAreaId, ?int $level = null)
    {
        $tournamentIds = $this->db->subQuery();
        $tournamentIds->where('ppArea_id', $ppAreaId);
        $tournamentIds->get('tournamentAreas', null, 'id');

        $this->db->where('id',$tournamentIds,'IN');
        $this->db->where('level', $level, '<=');
        
        return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function getUefa(){
		$this->db->where('country','Europe');
		return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function getForCountry(?string $country, int $level){
        if($country){
            $this->db->where('country',$country);
        }
        $this->db->where('level', $level, '<=');
        return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function update(int $id, array $data){
        $this->db->where('id', $id);
        $this->db->update('leagues', $data, 1);        
    }

    public function create(
        string $name, 
        string $tag, 
        ?string $country, 
        ?int $level, 
        ?int $parentId = null,
        ?string $ls_suffix = null,
    ) {
        $data = array(
            "name" => $name,
            "tag" => $tag,
            "country" => $country,
            "level" => $level,
            "parent_id" => $parentId,
            "ls_suffix" => $ls_suffix,
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