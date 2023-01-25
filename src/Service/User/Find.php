<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Repository\UserRepository;
use App\Service\RedisService;
use App\Service\UserParticipation;
use App\Service\Guess;

final class Find extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService,
        protected UserParticipation\Find $userParticipationFindService,
        protected Guess\Find $guessFindService
    ) {
    }

    public function isAdmin(int $userId){
        return $this->userRepository->isAdmin($userId);
    }
    
    public function adminGet() : ?array {
        $users = $this->userRepository->adminGet();
        foreach ($users as &$user) {
            $user['activeUserParticipations'] = $this->userParticipationFindService->getForUser($user['id'], null, true);
            $user['lastVerifiedGuesses'] = $this->guessFindService->getForUser($user['id'], true, 5);
            $user['lastLock'] = $this->guessFindService->lastLock($user['id']);
        }
        return $users;
    }
    
    public function getOneFromUsername(string $username, ?bool $allColumns=false){
        if(!$id = $this->idFromUsername($username)) return null;
        return $this->getOne($id, $allColumns);
    }

    public function getOne(int $userId, ?bool $allColumns=false) 
    {
        if (!$allColumns && self::isRedisEnabled() === true && $cached = $this->getUserFromCache($userId)) {
            return $cached;
        } 
        
        $user = $this->getUserFromDb($userId, $allColumns);

        if (!$allColumns && self::isRedisEnabled() === true){
            $this->saveInCache($userId, (object) $user);
        }

        return $user;
    }


}
