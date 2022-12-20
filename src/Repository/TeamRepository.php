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
        if(!$this->db->insert('teams',$data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        };
        return true;
    }

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
}
