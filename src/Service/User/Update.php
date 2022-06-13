<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RedisService;


final class Update extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService
    ) {
    }

    /**
     * @param array<string> $input
     */
    public function update(array $input, int $userId): object
    {
        $data = $this->validateUserData($input, $userId);
        /** @var User $user */
        $user = $this->userRepository->update($data);
        if (self::isRedisEnabled() === true) {
            $this->saveInCache($user->getId(), $user->toJson());
        }

        return $user->toJson();
    }

    /**
     * @param array<string> $input
     */
    private function validateUserData(array $input, int $userId): User
    {
        $user = $this->getUserFromDb($userId);
        $data = json_decode((string) json_encode($input), false);
        if (! isset($data->name) && ! isset($data->email)) {
            throw new \App\Exception\User('Enter the data to update the user.', 400);
        }
        if (isset($data->name)) {
            $user->updateName(self::validateUserName($data->name));
        }
        if (isset($data->email) && $data->email !== $user->getEmail()) {
            $this->userRepository->checkEmail($data->email);
            $user->updateEmail(self::validateEmail($data->email));
        }

        return $user;
    }
}
