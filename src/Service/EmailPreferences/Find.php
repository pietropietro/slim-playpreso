<?php

declare(strict_types=1);

namespace App\Service\EmailPreferences;

use App\Repository\EmailPreferencesRepository;
use App\Service\BaseService;

final class Find extends BaseService
{
    public function __construct(
        protected EmailPreferencesRepository $emailPreferencesRepository,
    ) {}

    public function getNeedLockReminder(){
        return $this->emailPreferencesRepository->getNeedLockReminder();
    }

    public function getForUser(int $userId){
        return $this->emailPreferencesRepository->getForUser($userId);
    }
}

