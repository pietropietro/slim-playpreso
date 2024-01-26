<?php

declare(strict_types=1);

namespace App\Repository;

final class ExternalApiSessionRepository extends BaseRepository
{

    public function getSession(){
        return $this->db->getValue('externalApiSession', 'session');
    }

    public function updateSession($newSession){
        $data = array('session' => $newSession);

        if(!$this->getSession()){
            return $this->db->insert('externalApiSession', $data);
        }
        return $this->db->update('externalApiSession', $data, 1);
    }

}