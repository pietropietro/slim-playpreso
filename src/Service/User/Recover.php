<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Repository\UserRecoverRepository;
use App\Service\StoPasswordReset;

final class Recover extends Base
{
    public function __construct(
        protected UserRecoverRepository $userRecoverRepository,
    ) {}

    public function validateToken(string $plainToken){
        if(!StoPasswordReset::isTokenValid($plainToken)){
            throw new \App\Exception\User('Invalid token.', 401);
        }        
        $hash = StoPasswordReset::calculateTokenHash($plainToken);
        $userRecover = $this->userRecoverRepository->getFromToken($hash);

        if(!$userRecover){
            throw new \App\Exception\User('Invalid token.', 401);
        }
        
        if (StoPasswordReset::isTokenExpired(new \DateTime($userRecover['created_at']))){
            throw new \App\Exception\User('Token expired.', 401);
        }
        return $userRecover;
    }

    public function saveRecoverToken(int $userId, string $hashedToken){
        //delete all previous entries for user
        $this->userRecoverRepository->deleteTokens($userId);
        $this->userRecoverRepository->create($userId, $hashedToken);
    }

    public function deleteTokens(int $userId){
        return $this->userRecoverRepository->deleteTokens($userId);
    }

}
