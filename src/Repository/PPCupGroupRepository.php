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
        $this->db->where('ppCup_id', $ppCupId);
        $this->db->where('started_at', true);
        $this->db->orderBy('level', 'desc');
        $level = $this->db->getValue('ppCupGroups', 'level', 1);
       
        return $level ?? 1;
    }

    function getTag(int $ppCupGroupId){
        $this->db->where('id', $ppCupGroupId);
        return $this->db->getValue('ppCupGroups','tag');
    }

    function getNotFull(int $ppCupId, int $level = 1, ?string $avoidFromTag = null){
       // Base query
        $query = "
            SELECT ppCupGroups.id, participants, COUNT(ups.id) 
            FROM ppCupGroups
            LEFT JOIN userParticipations ups ON ups.ppCupGroup_id = ppCupGroups.id 
            WHERE ppCupGroups.level = ? AND ppCupGroups.ppCup_id = ?
        ";

        // Parameters for the base query
        $params = [$level, $ppCupId];

        // Conditional part of the query based on 'avoidFromTag'
        if ($avoidFromTag !== null) {
            $query .= "
                AND ppCupGroups.id NOT IN (
                    SELECT ppCupGroup_id 
                    FROM userParticipations 
                    WHERE ppCup_id = ? AND level = ? AND from_tag = ?
                )
            ";
            // Adding parameters for the conditional part
            array_push($params, $ppCupId, $level, $avoidFromTag);
        }

        // Finalizing the query
        $query .= "
            GROUP BY ppCupGroups.id 
            HAVING COUNT(ups.id) < participants 
            ORDER BY COUNT(ups.id) ASC
        ";

        // Execute the query with parameter binding
        return $this->db->rawQuery($query, $params);
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

    function create(int $ppCupId, int $ppTournamentType_id, int $level, int $rounds, string $tag, ?int $participants=null){
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



    public function sumParticipantsOfLevel($ppCupId, $level){
        $this->db->where('ppCup_id', $ppCupId);
        $this->db->where('level', $level);
        return $this->db->getValue('ppCupGroups','sum(participants)');
    }

    public function countGroupsOfLevel($ppCupId, $level){
        $this->db->where('ppCup_id', $ppCupId);
        $this->db->where('level', $level);
        return $this->db->getValue('ppCupGroups','count(*)');
    }
}