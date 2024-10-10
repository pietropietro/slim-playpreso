<?php

declare(strict_types=1);

namespace App\Repository;

final class PushNotificationPreferencesRepository extends BaseRepository
{

    public function hasRejected(int $userId, string $eventType){
        $this->db->where('user_id', $userId);
        $this->db->where('event_type_rejected', $eventType);
        return $this->db->has('pushNotificationPreferences');
    }

    public function get(int $user_id){
        $this->db->where('user_id', $user_id);
        return $this->db->get('pushNotificationPreferences');
    }

    public function create(int $userId, string $eventType){
        $data = array(
            'user_id' => $userId,
            'event_type_rejected' => $eventType
        );
        return $this->db->insert('pushNotificationPreferences', $data);
    }

    public function delete(int $userId, string $eventType){
        $this->db->where('user_id', $userId);
        $this->db->where('event_type_rejected', $eventType);
        return $this->db->delete('pushNotificationPreferences', 1);
    }

}