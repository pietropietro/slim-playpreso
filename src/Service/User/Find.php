<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Repository\UserRepository;
use App\Service\RedisService;
use App\Service\UserParticipation;
use App\Service\Guess;
use App\Service\Trophy;
use App\Service\PPRanking;
use App\Service\MOTD;
use App\Service\Flash;

final class Find extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService,
        protected UserParticipation\Find $userParticipationFindService,
        protected Guess\Find $guessFindService,
        protected Trophy\Find $trophyFindService,
        protected PPRanking\Find $ppRankingFindService,
        protected MOTD\Leader $ppMotdLeaderService,
        protected Flash\Find $flashFindService
    ) {
    }

    public function isAdmin(int $userId){
        return $this->userRepository->isAdmin($userId);
    }
    
    public function adminGet(
        ?int $page = null, 
        ?int $limit = null
    ) : ?array {

        $offset = ($page - 1) * $limit;

        $data = $this->userRepository->adminGet($offset, $limit);
        
        foreach ($data['users'] as &$user) {
            $user['activeUserParticipations'] = $this->userParticipationFindService->getForUser(
                $user['id'], null, started: null, finished:false
            );
            $user['lastVerifiedGuesses'] = $this->guessFindService->getLastVerified($user['id'], null, 5);
            $user['lastLock'] = $this->guessFindService->lastLock($user['id']);
        }
        return $data;
    }
    
    public function getOneFromUsername(string $username, ?bool $sensitiveColumns=false){
        if (strpos($username, 'deleted') === 0) {
            return null;
        }
        
        if(!$id = $this->idFromUsername($username)) return null;
        return $this->getOne($id, $sensitiveColumns);
    }

    private const REDIS_KEY_USER = 'user:%s';

    public function getOne(int $userId, ?bool $sensitiveColumns=false) 
    {
        if (!$sensitiveColumns && self::isRedisEnabled() === true ) {
            $cachedUser = $this->getUserFromCache($userId);
            if ($cachedUser !== null) {
                return $cachedUser;
            }
        } 
        
        $user = $this->getUserFromDb($userId, $sensitiveColumns);

        $user['ppRanking'] = $this->ppRankingFindService->getForUser($userId);
        $user['trophies_count'] = $this->trophyFindService->getTrophies($userId, null, true);
        $user['motdLeader'] = $this->ppMotdLeaderService->getMotdLeader()['user_id'] == $user['id'];
        $user['flashLeader'] = $this->flashFindService->getFlashLeader()['user_id'] == $user['id'];
        
        if (!$sensitiveColumns && self::isRedisEnabled() === true){
            $this->saveUserInCache($userId, $user);
        }

        return $user;
    }

    protected function getUserFromCache(int $userId)
    {
        $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_USER, $userId));
        return $this->redisService->get($redisKey); // This returns null if not found or the user data if found
    }

    protected function saveUserInCache(int $userId, array $user): void
    {
        $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_USER, $userId));
        $expiration = 2 * 60 * 60; 
        $this->redisService->setex($redisKey, $user, $expiration); 
    }

}
