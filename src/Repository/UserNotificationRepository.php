<?php

declare(strict_types=1);

namespace App\Repository;

final class UserNotificationRepository extends BaseRepository
{
    public function create(int $userId, string $eventType, int $eventId){
        $data = array(
            'user_id' => $userId,
            'event_type' => $eventType,     
            'event_id' => $eventId        
        );  
        return $this->db->insert('userNotifications', $data);
    }

    public function deleteForEventTypeAndUser(string $eventType, int $userId){
        $this->db->where('id', $ids, 'IN');
        return $this->db->delete('userNotifications');
    }

    public function has(int $userId, string $eventType, int $eventId) : bool{
        $this->db->where('user_id', $userId);
        $this->db->where('event_type', $eventType);
        $this->db->where('event_id', $eventId);
        return $this->db->has('userNotifications');
    }

    public function getForUser(int $userId){
        $this->db->where('user_id', $userId);
        return $this->db->get('userNotifications', 30);
    }

    public function getUnread(int $userId){
        $this->db->where('user_id', $userId);
        $this->db->where('updated_at IS NULL');
        $this->db->orderBy('created_at','desc');
        return $this->db->get('userNotifications', 30);
    }

    public function setRead(int $userId){
        $this->db->where('user_id', $userId);
        $data = array(
            "updated_at" => $this->db->now()
        );
        return $this->db->update('userNotifications', $data);
    }
}