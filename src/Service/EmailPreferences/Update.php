<?php

declare(strict_types=1);

namespace App\Service\EmailPreferences;

use App\Repository\EmailPreferencesRepository;
use App\Service\BaseService;

final class Update extends BaseService
{
    public function __construct(
        protected EmailPreferencesRepository $emailPreferencesRepository,
    ) {}

    public function update(int $userId, array $data){
        $this->emailPreferencesRepository->update($userId, $data);
    }
}

