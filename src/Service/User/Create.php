<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;

final class Create extends Base
{
    /**
     * @param array<string> $input
     */
    public function create(array $input)
    {
        $data = $this->validateUserData($input);
        $userId = $this->userRepository->create($data);

        return $userId;
    }

    /**
     * @param array<string> $input
     */
    private function validateUserData(array $input)
    {
        $user = json_decode((string) json_encode($input), false);

        if (! isset($user->username)) {
            throw new \App\Exception\User('The field "username" is required.', 400);
        }
        if (! isset($user->email)) {
            throw new \App\Exception\User('The field "email" is required.', 400);
        }
        if (! isset($user->password)) {
            throw new \App\Exception\User('The field "password" is required.', 400);
        }

        $this->userRepository->checkEmail($user->email);
        $this->userRepository->checkUsername($user->username);
        
        $user->username = self::validateUserName($user->username);
        $user->email = self::validateEmail($user->email);
        
	    $hash = password_hash($user->password, PASSWORD_BCRYPT);
        $encoded = base64_encode($hash);
        $user->password = $encoded;

        //TODO get country from IP
        $user->country = "test";
        
        return $user;
    }
}
