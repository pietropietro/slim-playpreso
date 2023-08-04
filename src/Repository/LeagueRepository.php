<?php

declare(strict_types=1);

namespace App\Repository;

final class LeagueRepository extends BaseRepository
{
    private $columnsNoStandings = "id, name, tag, country, ls_suffix, parent_id, level";
    private $adimnColumnsNoStandings = "id, name, tag, country, ls_suffix, parent_id, updated_at, level";
    private $columnsWithStandings = "id, name, tag, country, ls_suffix, parent_id, standings";

    public function get(?int $maxLevel=null){
        if($maxLevel) $this->db->where('level', $maxLevel, '<=');
        return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function adminGet(){
        return $this->db->get('leagues', null, $this->adimnColumnsNoStandings);
    }

    public function getOne(int $id, ?bool $withStandings = false){
        $this->db->where('id', $id);
        return $this->db->getOne(
            'leagues', 
            $withStandings ? $this->columnsWithStandings : $this->columnsNoStandings
        );
    }

    public function getWithChildren(int $id){
        $this->db->where('id', $id);
        $this->db->orWhere('parent_id', $id);
        return $this->db->get('leagues',null, $this->columnsNoStandings);
    }

    public function getForArea(int $ppAreaId, ?int $level = null)
    {
        $this->db->where('ppArea_id', $ppAreaId);
        $leagueIds = $this->db->getValue('ppAreaLeagues', 'league_id', null);

        $leagueCountries = $this->db->subQuery();
        $leagueCountries->where('ppArea_id', $ppAreaId);
        $leagueCountries->get('ppAreaLeagues', null, 'country');

        $this->db->where('id', $leagueIds, 'IN');
        $this->db->orWhere('parent_id', $leagueIds, 'IN');
        $this->db->orWhere('country', $leagueCountries, 'IN');
        
        if($level)$this->db->where('level', $level, '<=');
        
        return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    //TODO move to pparea
    function getPPAreaExtraLeagues(int $ppAreaId){
        $leagueIds = $this->db->subQuery();
        $leagueIds->where('ppArea_id', $ppAreaId);
        $leagueIds->getValue('ppAreaLeagues', 'league_id', null);

        $this->db->where('id',$leagueIds,'IN');        
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


    //retrieves leagues with unverified matches in the PAST
    public function getNeedPastData(bool $havingGuesses = false, ?string $fromTime = null){
        $this->db->join("matches m", "m.league_id=l.id", "INNER");
        if($havingGuesses)$this->db->join("guesses g", "g.match_id=m.id", "INNER");
        $this->db->where('m.verified_at IS NULL');
        $this->db->where('m.notes IS NULL');
        $start = date("Y-m-d H:i:s", strtotime($fromTime ?? '-2 days'));
        $finish = date("Y-m-d H:i:s", strtotime('-110 minutes'));
        $this->db->where('m.date_start', array($start, $finish), 'BETWEEN');
        return $this->db->query("select distinct ls_suffix, l.id, l.tag, l.name, l.updated_at, l.country from leagues l");
    }

    //retrieves leagues with no matches in the FUTURE
    public function getNeedFutureData(){
        $result = $this->db->query("select l.id from leagues l left join matches m on l.id=m.league_id where m.date_start > now() group by l.id");
        $idsWithFuture = array_column($result, 'id');       
        $this->db->where('id', $idsWithFuture, 'NOT IN');
        return $this->db->get('leagues', null,$this->adimnColumnsNoStandings);
    }

}