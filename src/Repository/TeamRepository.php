<?php

declare(strict_types=1);

namespace App\Repository;

final class TeamRepository extends BaseRepository
{
    public function get(array $ids){
        $this->db->where('id',$ids,'IN');
        return $this->db->get('teams');
    }

    public function getOne(int $id){
        $this->db->where('id',$id);
        return $this->db->getOne('teams');
    }

    public function idFromExternal(int $ls_id) : int{
        $this->db->where('ls_id',$ls_id);
        $team = $this->db->getOne('teams');
        return $team['id'];
    }
}
