<?php

declare(strict_types=1);

namespace App\Repository;

final class ExternalApiSessionRepository extends BaseRepository
{

    public function getSession(){
        return $this->db->getValue('external_api_session', 'session');
    }

    public function updateSession($newSession){
        $data = array('session' => $newSession);

        if(!$this->getSession()){
            return $this->db->insert('external_api_session', $data);
        }
        return $this->db->update('external_api_session', $data, 1);
    }

}