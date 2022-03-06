<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Exception\User;
use Firebase\JWT\JWT;

final class Login extends Base
{
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
        $token = [
            'username' => $user['username'],
            'id' => $user['id'],
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60),
        ];

        $jwt = JWT::encode($token, $_SERVER['SECRET_KEY']);
        
        return [
            'authorization' => 'Bearer ' . $jwt,
            'user' => $user
        ];
    }
}
