<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPCupRepository extends BaseRepository
{
    public function get(?array $ids, ?int $ppTournamentTypeId) {
        if($ids)$this->db->where('id', $ids, 'IN');
        if($ppTournamentTypeId)$this->db->where('ppTournamentType_id', $ppTournamentTypeId);
        $this->db->orderBy('created_at','DESC');
        return $this->db->get('ppCups');
    }

    function getOne($uniqueVal, bool $is_slug = false){
        $column = $is_slug ? 'slug' : 'id';
        $this->db->where($column, $uniqueVal);
        $this->db->orderBy('created_at', 'DESC');
        return $this->db->getOne('ppCups');
    }

    public function create(int $ppTournamentTypeId, ?string $slug){
        $data = array(
            "ppTournamentType_id" => $ppTournamentTypeId,
            "created_at" => $this->db->now(),
            "slug" => $slug,
        );
        return $this->db->insert('ppCups',$data);
    }

    public function getJoinable(int $ppTournamentTypeId){
        $this->db->where('ppTournamentType_id', $ppTournamentTypeId);
        $this->db->where('finished_at IS NULL');
        $this->db->where('started_at IS NULL');
        return $this->db->getOne('ppCups');
    }

    public function setFinished(int $id){
        $data = array(
            "finished_at" => $this->db->now(),
        );
        $this->db->where('id', $id);
        $this->db->update('ppCups', $data, 1);
    }

    function setStarted(int $id) {
        $data = array(
            "started_at" => $this->db->now(),
        );
        $this->db->where('id', $id);
        $this->db->update('ppCups', $data, 1);
    }
        
}