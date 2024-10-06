<?php

declare(strict_types=1);

namespace App\Repository;

final class DeviceTokenRepository extends BaseRepository
{

    public function save(int $userId, string $token, string $platform) {

        // Check if the token already exists
        $existingToken = $this->db->where('user_id', $userId)
            ->where('token', $token)
            ->where('platform', $platform)
            ->getOne('deviceTokens');

        if ($existingToken) return;

        $data = [
            'user_id' => $userId,
            'token' => $token,
            'platform' => $platform
        ];
        return $this->db->insert('deviceTokens', $data);
    }
    

    public function hasToken(int $userId){
        $this->db->where('user_id', $userId);
        return $this->db->has('deviceTokens');
    }

    public function getTokensByUserId(int $userId)
    {
        $this->db->where('user_id', $userId);
        return $this->db->get('deviceTokens');
    }

    public function remove(int $userId, string $deviceToken){
        $this->db->where('user_id', $userId);
        $this->db->where('token', $deviceToken);
        $this->db->delete('deviceTokens',1);
    }

    
}