<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPCupGroupRepository extends BaseRepository
{
    public function get(array $ids) {
        $this->db->where('id', $ids, 'IN');
        $ppCupGroups=$this->db->get('ppCupGroups');
        if (! $ppCupGroups) {
            throw new NotFound('ppCupGroups not found.', 404);
        }   
        return $ppCupGroups;
    }

    function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('ppCupGroups');
    }

    function getIds(int $ppCupId){
        $this->db->where('ppCup_id', $ppCupId);
        return $this->db->getValue('ppCupGroups', 'id', null);
    }

    function getForCup(int $ppCupId){
        $this->db->where('ppCup_id', $ppCupId);
        return $this->db->get('ppCupGroups');
    }

    function getJoinable(int $ppCupId){
        $groups = $this->db->query('
            select ppcupgroups.id, participants, count(ups.id) 
            from ppcupgroups
            left  join userparticipations ups on ups.ppcupgroup_id=ppcupgroups.id 
            where ppcupgroups.level=1 and ppcupgroups.ppcup_id='.$ppCupId.' 
            group by ppcupgroups.id 
            having count(ups.id) < participants',
        1);
        return $groups[0] ?? null;
    }

    function create(int $ppCupId, int $level, int $rounds, string $tag, int $participants){
        $data = array(
            "ppCup_id" => $ppCupId,
            "level" => $level,
            "rounds" => $rounds,
            "created_at" => $this->db->now(),
            "tag" => $tag,
            "participants" => $participants
        );
        return $this->db->insert('ppCupGroups',$data);
    }

}