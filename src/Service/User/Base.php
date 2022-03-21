<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\GuessRepository;
use App\Repository\MatchRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Service\BaseService;
use App\Service\RedisService;
use Respect\Validation\Validator as v;

abstract class Base extends BaseService
{
    private const REDIS_KEY = 'user:%s';

    //TODO only use shared ones
    //create specific constructor for other user services
    public function __construct(
        protected UserRepository $userRepository,
        protected PPLeagueRepository $ppLeagueRepository,
        protected UserParticipationRepository $UserParticipationRepository,
        protected GuessRepository $guessRepository,
        protected MatchRepository $matchRepository,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected RedisService $redisService
    ) {
    }

    protected static function validateUserName(string $username): string
    {
        if (! v::alnum('ÁÉÍÓÚÑáéíóúñ.')->noWhitespace()->length(1, 10)->validate($username)) {
            throw new \App\Exception\User('Invalid username.', 400);
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

    protected function getAvailablePPLeagueTypesFromCache(int $userId)
    {
        $redisKey = sprintf('ppLeagueTypes-' . self::REDIS_KEY, $userId);
        $key = $this->redisService->generateKey($redisKey);
        if ($this->redisService->exists($key)) {
            $data = $this->redisService->get($key);
            return json_decode((string) json_encode($data), false);
        } 
        return null;
    }

    

    protected function getUserFromDb(int $userId)
    {
        $user =  $this->userRepository->getUser($userId);
        return $user;
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
