<?php

declare(strict_types=1);

namespace App\Repository;

final class TeamRepository extends BaseRepository
{
    
    public function create(int $ls_id, string $name, string $country){
        $data = array(
			"ls_id" => $ls_id,
			"name" => $name,
			"country" => $country,
	    );
        if(!$newId = $this->db->insert('teams',$data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        };
        return $newId;
        
    }

    // public function getInternalExternalIdPair(){
    //     $this->db->orderBy('rand()');
    //     return $this->db->get('teams',null ,'id, ls_id');
    // }

    public function getOne(int $id, bool $is_external_id = false){
        $column = !!$is_external_id ? 'ls_id' : 'id';
        $this->db->where($column, $id);
        //do not return ls_id
        $columns = array('id','country','name');
        return $this->db->getOne('teams', $columns);
    }

    public function idFromExternal(int $ls_id) : ?int{
        $this->db->where('ls_id',$ls_id);
        $team = $this->db->getOne('teams');
        return $team ? $team['id'] : null;
    }

    // W || D || L
    public function getLastResults(int $id, int $limit = 3){
        $this->db->where("(home_id={$id} or away_id={$id}) and verified_at is not null");
        $this->db->orderBy('date_start');
        $select = "case 
            WHEN score_home < score_away THEN CASE WHEN home_id={$id} THEN 'L' ELSE 'W' END 
            WHEN score_home > score_away THEN CASE WHEN home_id={$id} THEN 'W' ELSE 'L' END 
            WHEN score_home = score_away THEN 'D' end as wdl";
        return $this->db->get('matches',$limit, $select);
    }
}
