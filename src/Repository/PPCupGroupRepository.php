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
        if($finished !== null)$this->db->where('finished_at IS '. ($finished ? ' NOT ' : '').'NULL');
        return $this->db->get('ppCupGroups');
    }

    public function getCurrentCupLevel(int $ppCupId) :int {
        $level = $this->db->query(
            'select level from ppCupGroups where id IN 
                (select ppCupGroup_id from ppRounds where ppCupGroup_id IN 
                    (select id from ppCupGroups where ppCup_id = '.$ppCupId.')
                )
            order by level desc limit 1;      
        ');
        return $level[0]['level'] ?? 1;
    }

    function getNotFull(int $ppCupId, int $level = 1){
        //raw query because of the 'having' clause
        //which otherwise (OO) wrongly translates 'participants' as a string
        //and not as the table column value

        return $this->db->query('
            select ppCupGroups.id, participants, count(ups.id) 
            from ppCupGroups
            left  join userParticipations ups on ups.ppCupGroup_id=ppCupGroups.id 
            where ppCupGroups.level='.$level.' and ppCupGroups.ppCup_id='.$ppCupId.' 
            group by ppCupGroups.id 
            having count(ups.id) < participants
            order by count(ups.id) ASC',
        1);

    }

    public function getPaused() {
        $sql = 'SELECT ppcg.*
            FROM ppCupGroups ppcg
            WHERE ppcg.started_at IS NOT NULL
            AND ppcg.finished_at IS NULL
            AND NOT EXISTS (
                SELECT 1
                FROM ppRounds ppr
                INNER JOIN ppRoundMatches pprm ON pprm.ppRound_id = ppr.id
                INNER JOIN matches m ON pprm.match_id = m.id
                WHERE ppr.ppCupGroup_id = ppcg.id
                AND m.verified_at IS NULL
            )';
        return $this->db->query($sql);
    }

    function create(int $ppCupId, int $ppTournamentType_id, int $level, int $rounds, string $tag, int $participants){
        $data = array(
            "ppCup_id" => $ppCupId,
            "ppTournamentType_id" => $ppTournamentType_id,
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
        return $this->db->update('ppCupGroups', $data, 1);
    }

    function setStarted(int $id) {
        $data = array(
            "started_at" => $this->db->now(),
        );
        $this->db->where('id', $id);
        $this->db->update('ppCupGroups', $data, 1);
    }



}