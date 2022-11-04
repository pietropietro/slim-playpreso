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

    function getForCup(int $ppCupId, ?int $level = null, ?bool $finished = null){
        $this->db->where('ppCup_id', $ppCupId);
        if($level)$this->db->where('level', $level);
        if($finished != null)$this->db->where('finished_at IS '. ($finished ? ' NOT ' : '').'NULL');
        return $this->db->get('ppCupGroups');
    }

    function getNotFull(int $ppCupId, int $level){
        //raw query because of the 'having' clause
        //which otherwise (OO) wrongly translates 'participants' as a string
        //and not as the table column value

        return $this->db->query('
            select ppcupgroups.id, participants, count(ups.id) 
            from ppcupgroups
            left  join userparticipations ups on ups.ppcupgroup_id=ppcupgroups.id 
            where ppcupgroups.level='.$level.' and ppcupgroups.ppcup_id='.$ppCupId.' 
            group by ppcupgroups.id 
            having count(ups.id) < participants
            order by count(ups.id) ASC',
        1);

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

    public function setFinished(int $id){
        $data = array(
            "finished_at" => $this->db->now(),
        );
        $this->db->where('id', $id);
        $this->db->update('ppCupGroups', $data, 1);
    }

    function setStarted(int $id) {
        $data = array(
            "started_at" => $this->db->now(),
        );
        $this->db->where('id', $id);
        $this->db->update('ppCupGroups', $data, 1);
    }



}