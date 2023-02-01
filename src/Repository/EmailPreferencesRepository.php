<?php

declare(strict_types=1);

namespace App\Repository;

final class EmailPreferencesRepository extends BaseRepository
{

    public function getOne(int $userId){
        $this->db->where('user_id', $userId);
        return $this->db->getOne('emailPreferences');
    }

    public function update(int $userId, array $data){
        $this->db->where('user_id', $userId);
        return $this->db->update('emailPreferences', $data, 1);
    }

    // returns ( 264 | 1483,1553   | pietro@playpreso.com )
    public function getNeedLockReminder(){
        $this->db->join("users u", "u.id=g.user_id", "INNER");
        $this->db->join("matches m", "m.id=g.match_id", "INNER");
        $this->db->join("emailPreferences ep", "ep.id=g.user_id", "INNER");
       
        $this->db->where('g.guessed_at is null');
        $this->db->where('g.verified_at is null');
        $this->db->where('m.verified_at is null');

        $this->db->where('ep.lock_reminder', 1);

        $start = date("Y-m-d H:i:s");
        $finish = date("Y-m-d H:i:s", strtotime('+23 hours'));
        $this->db->where('m.date_start', array($start, $finish), 'BETWEEN');

        $columns='g.user_id, group_concat(g.id) as guesses_id_concat, u.email, u.username';
        return $this->db->get('guesses',null, $columns);
    }

}