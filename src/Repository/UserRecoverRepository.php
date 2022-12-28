<?php

declare(strict_types=1);

namespace App\Repository;

final class UserRecoverRepository extends BaseRepository
{
    public function create(int $userId, string $hash){
        $data = array(
            'user_id' => $userId,
            'hashed_token' => $hash        
        );  
        $this->db->insert('userRecover', $data);
    }

    public function deleteForUser(int $userId){
        $this->db->where('user_id', $userId);
        $this->db->delete('userRecover');
    }
}