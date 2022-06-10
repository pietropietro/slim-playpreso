<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Exception\User;
use App\Middleware\Auth;
use App\Repository\UserRepository;
use App\Service\RedisService;


final class Login extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService
    ) {
    }

    /**
     * @param array<string> $input
     */
    public function login(array $input)
    {
        $data = json_decode((string) json_encode($input), false);
        if (! isset($data->username)) {
            throw new User('The field "username" is required.', 400);
        }
        if (! isset($data->password)) {
            throw new User('The field "password" is required.', 400);
        }

        $user = $this->userRepository->loginUser($data->username, $data->password);
        
        return [
            'Authorization' => Auth::createToken($user['username'], $user['id']),
            'user' => $user
        ];
    }
}
