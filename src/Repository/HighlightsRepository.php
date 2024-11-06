<?php

declare(strict_types=1);

namespace App\Repository;


final class HighlightsRepository extends BaseRepository
{

    public function getLastPresos(
        ?int $offset = null, 
        ?int $limit = 1
    ){
        $this->db->join('ppRoundMatches pprm', 'pprm.id=g.ppRoundMatch_id', 'INNER');
        $this->db->groupBy('pprm.match_id');
        $this->db->where('PRESO',1);
        $this->db->orderBy('verified_at','desc');
        $columns = 'group_concat(g.id) as ids, pprm.match_id';
        $result =  $this->db->get('guesses g', [$offset, $limit], $columns);
        return $result;
    }

}