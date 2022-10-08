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

    public function hasOneUnfinished(int $ppTournamentTypeId){
        $this->db->where('ppTournamentType_id', $ppTournamentTypeId);
        $this->db->where('finished_at IS NULL');
        return $this->db->has('ppCups');
    }

}