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
        $user = $this->userRepository->create($data);

        if (self::isRedisEnabled() === true) {
            $this->saveInCache($user->getId(), $user->toJson());
        }
        return $user;
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

        $this->userRepository->checkUserByEmail($user->email);
        $this->userRepository->checkUserByUsername($user->username);
        
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
