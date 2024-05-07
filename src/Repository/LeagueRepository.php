<?php

declare(strict_types=1);

namespace App\Repository;

final class LeagueRepository extends BaseRepository
{
    private $columnsNoStandings = "id, name, tag, country, parent_id, level";
    private $adimnColumnsNoStandings = "id, name, tag, country, ls_suffix, ls_410, parent_id, updated_at, level";
    private $adimnColumnsL = "l.id, l.name, l.tag, l.country, l.ls_suffix, l.ls_410, l.parent_id, l.updated_at, l.level";
    private $columnsWithStandings = "id, name, tag, country, parent_id, standings, level";

    public function get(?int $maxLevel=null){
        if($maxLevel) $this->db->where('level', $maxLevel, '<=');
        return $this->db->get('leagues', null, $this->columnsNoStandings);
    }

    public function adminGet(
        ?string $country = null, 
        ?int $offset = null, 
        ?int $limit = 200,
        ?bool $parentOnly = null
    ){
        if ($country && $country != 'ALL') {
            $this->db->where('l.country', $country);
        }

        if ($parentOnly) {
            // $offset = 0;
            $limit = 100;
            $this->db->where('id = parent_id');
        }
    
        $leagues = $this->db->withTotalCount()->get(
            'leagues l', 
            [$offset, $limit], 
            $this->adimnColumnsL
        );
    
        return [
            'leagues' => $leagues,
            'total' => $this->db->totalCount,
        ];
    }
    

    public function adminGetCountries(){
        $countries = $this->db->rawQuery("SELECT DISTINCT country FROM leagues where country is not null ORDER BY country ASC");
        return array_column($countries, 'country');
    }

    public function getOne(int $id,  ?bool $admin = false ,?bool $withStandings = false){
        $this->db->where('id', $id);
        return $this->db->getOne(
            'leagues', 
            $admin ? $this->adimnColumnsNoStandings :
            ($withStandings ? $this->columnsWithStandings : $this->columnsNoStandings)
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

    //it means the http external api call was successful
    //it does NOT mean there was new data
    public function setFetched(int $id){
        $data = array(
            "updated_at" => $this->db->now()
        );
        $this->db->where('id', $id);
        $this->db->update('leagues', $data, 1);        
    }

    public function updateStandings(int $id, string $standings_json){
        $data = array(
            "standings" => $standings_json
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
        $sql = '
                select l.id from leagues l 
                left join matches m on l.id=m.league_id 
                where m.date_start > now() 
                and l.ls_410 is not true
                group by l.id
        ';
        $result = $this->db->query($sql);
        $idsWithFuture = array_column($result, 'id');       
        $this->db->where('id', $idsWithFuture, 'NOT IN');

        $after = date("Y-m-d H:i:s", strtotime('5 days ago'));
        $this->db->where('updated_at', $after, '<');
        $this->db->orWhere('updated_at IS NULL');

        return $this->db->get('leagues', null,$this->adimnColumnsNoStandings);
    }

}