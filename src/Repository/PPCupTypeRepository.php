<?php

declare(strict_types=1);

namespace App\Repository;


final class PPCupTypeRepository extends BaseRepository
{

    function getOne($id){
        $this->db->where('id',$id);
        return $this->db->getOne('ppCupTypes');
    }

}