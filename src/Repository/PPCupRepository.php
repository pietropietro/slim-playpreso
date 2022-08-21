<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPCupRepository extends BaseRepository
{
    public function get(array $ids) {
        $this->db->where('id', $ids, 'IN');
        $ppCups=$this->db->get('ppCups');
        if (! $ppCups) {
            throw new NotFound('ppCups not found.', 404);
        }   
        return $ppCups;
    }

    function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('ppCups');
    }
}