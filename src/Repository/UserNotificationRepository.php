<?php

declare(strict_types=1);

namespace App\Repository;

final class UserNotificationRepository extends BaseRepository
{
    public function create(int $userId, string $eventType, ?int $eventId = null){
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

    public function deleteOld(){
        $old = (new \DateTime('-30 days'))->format('Y-m-d');
        $this->db->where('created_at', $old, '<');
        return $this->db->delete('userNotifications');
    }

    public function has(int $userId, string $eventType, ?int $eventId = null) : bool{
        $this->db->where('user_id', $userId);
        $this->db->where('event_type', $eventType);

        if($eventId)$this->db->where('event_id', $eventId);
        
        return $this->db->has('userNotifications');
    }

    public function getForUser(int $userId){
        $this->db->where('user_id', $userId);
        return $this->db->get('userNotifications', 30);
    }

    public function getUnread(int $userId, int $page=1, int $limit=10){
        $this->db->where('user_id', $userId);
        $this->db->where('updated_at IS NULL');
        $this->db->orderBy('created_at','desc');
        return $this->db->get('userNotifications', [$offset, $limit], '*');
    }

    public function countUnread(int $userId){
        $this->db->where('user_id', $userId);
        $this->db->where('updated_at IS NULL');
        return $this->db->getValue ("userNotifications", "count(*)");
    }

    public function setRead(int $userId){
        $this->db->where('user_id', $userId);
        $data = array(
            "updated_at" => $this->db->now()
        );
        return $this->db->update('userNotifications', $data);
    }
}