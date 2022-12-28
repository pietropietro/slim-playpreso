<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Repository\UserRecoverRepository;

final class Recover extends Base
{
    public function __construct(
        protected UserRecoverRepository $userRecoverRepository,
    ) {}

    public function saveRecoverToken(int $userId, string $hashedToken){
        //delete all previous entries for user
        $this->userRecoverRepository->deleteForUser($userId);
        $this->userRecoverRepository->create($userId, $hashedToken);
    }

}
