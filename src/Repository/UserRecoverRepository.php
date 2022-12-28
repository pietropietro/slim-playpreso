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

    public function getFromToken(string $hashedToken){
        $this->db->join("users u", "u.id=ur.user_id", "INNER");
        $this->db->where('hashed_token', $hashedToken);
        return $this->db->getOne("userRecover ur", "username, id");
    }

    public function deleteTokens(int $userId){
        $this->db->where('user_id', $userId);
        $this->db->delete('userRecover');
    }
}