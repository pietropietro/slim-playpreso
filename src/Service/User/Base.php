<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RedisService;
use App\Service\BaseService;
use Respect\Validation\Validator as v;

abstract class Base extends BaseService
{
    private const REDIS_KEY = 'user:%d';

    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService
    ) {
    }

    protected static function validateUserName(string $username): string
    {
        if (! v::alnum('ÁÉÍÓÚÑáéíóúñ.')->noWhitespace()->length(1, 10)->validate($username)) {
            throw new \App\Exception\User('Invalid username.', 400);
        }

        // Check if the username starts with 'deleted'
        if (strpos($username, 'deleted') === 0) {
            throw new \App\Exception\User('Invalid username.', 403);
        }
        
        return $username;
    }

    protected static function validateEmail(string $emailValue): string
    {
        $email = filter_var($emailValue, FILTER_SANITIZE_EMAIL);
        if (! v::email()->validate($email)) {
            throw new \App\Exception\User('Invalid email', 400);
        }

        return (string) $email;
    }

    protected function getUserFromCache(int $userId)
    {
        $redisKey = sprintf(self::REDIS_KEY, $userId);
        $key = $this->redisService->generateKey($redisKey);
        if ($this->redisService->exists($key)) {
            $data = $this->redisService->get($key);
            return json_decode((string) json_encode($data), false);
        } 
        return null;
    }

    
    protected function getUserFromDb(int $userId, ?bool $sensitiveColumns= false)
    {
        $user =  $this->userRepository->getOne($userId, $sensitiveColumns);
        return $user;
    }

    public function idFromUsername(string $username)
    {
        $id =  $this->userRepository->getId($username);
        return $id;
    }

    protected function saveInCache(int $id, object $user): void
    {
        $redisKey = sprintf(self::REDIS_KEY, $id);
        $key = $this->redisService->generateKey($redisKey);
        $this->redisService->setex($key, $user);
    }

    protected function deleteFromCache(int $userId): void
    {
        $redisKey = sprintf(self::REDIS_KEY, $userId);
        $key = $this->redisService->generateKey($redisKey);
        $this->redisService->del([$key]);
    }
}
