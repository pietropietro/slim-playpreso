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

        if ($existingToken) {
            // Update existing record
            $data = ['updated_at' => date('Y-m-d H:i:s')];
            $this->db->where('id', $existingToken['id']);
            return $this->db->update('deviceTokens', $data);
        } else {
            $data = [
                'user_id' => $userId,
                'token' => $token,
                'platform' => $platform
            ];
            return $this->db->insert('deviceTokens', $data);
        }
    }

    public function getTokensByUserId(int $userId)
    {
        $this->db->where('user_id', $userId);
        return $this->db->get('deviceTokens');
    }
    
}