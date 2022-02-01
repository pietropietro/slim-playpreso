<?php

declare(strict_types=1);

namespace App\Service\User;

final class Find extends Base
{
    public function getOne(int $userId)
    {
        if (self::isRedisEnabled() === true) {
            $user = $this->getUserFromCache($userId);
        } else {
            // $user = $this->getUserFromDb($userId)->toJson();
            $user = $this->getUserFromDb($userId);
        }

        return $user;
    }
}
