<?php

declare(strict_types=1);

namespace App\Service\Flash;

use App\Service\BaseService;
use App\Repository\FlashRepository;

/**
 * Encapsulates "finding" logic (read operations).
 */
final class Find extends BaseService
{
    public function __construct(
        protected FlashRepository $flashRepository
    ) {}

    /**
     * Checks if there's at least one flash match on a given date (YYYY-mm-dd).
     */
    public function hasFlashForDate(string $isoDate): bool
    {
        $rows = $this->flashRepository->getFlashMatchesByDate($isoDate);
        return !empty($rows);
    }

   
    public function getLastFlash(?string $dateString = null, ?bool $verified = null): ?array
    {
        return $this->flashRepository->getLastFlash($dateString, $verified);
    }

    public function getNextFlash(): ?array
    {
        return $this->flashRepository->getNextFlash();
    }

    public function getCurrentFlash(): ?array
    {
        return $this->flashRepository->getCurrentFlash();
    }
}
