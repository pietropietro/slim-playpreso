<?php

declare(strict_types=1);

namespace App\Service\Flash;

use App\Service\BaseService;
use App\Service\PPRoundMatch;
use App\Repository\FlashRepository;

/**
 * Encapsulates "finding" logic (read operations).
 */
final class Find extends BaseService
{
    public function __construct(
        protected FlashRepository $flashRepository,
        protected PPRoundMatch\Find $ppRoundMatchFindService,
    ) {}

    /**
     * Checks if there's at least one flash match on a given date (YYYY-mm-dd).
     */
    public function hasFlashForDate(string $isoDate): bool
    {
        $rows = $this->flashRepository->getFlashMatchesByDate($isoDate);
        return !empty($rows);
    }

    public function getOne(int $ppRoundMatchId, int $userId){
        $pprmFlash = $this->ppRoundMatchFindService->getOne(
            $ppRoundMatchId,
            true,
            $userId,
            true,
            false
        );
        return $pprmFlash;
    }

    public function get(bool $verified = true, int $page=1, int $limit=10){
        $offset = ($page - 1) * $limit;
        $flashPPRMs = $this->flashRepository->get($verified, $offset, $limit);

        foreach($flashPPRMs as &$flash){
            $this->ppRoundMatchFindService->enrich($flash, withGuesses:true); 
        }
        return $flashPPRMs;

    }

   
    public function getLastFlash(?string $dateString = null, ?bool $verified = null, ?int $userId = null): ?array
    {
        $pprmFlash = $this->flashRepository->getLastFlash($dateString, $verified);
        $this->ppRoundMatchFindService->enrich($pprmFlash, true, $userId);
        return $pprmFlash;
    }

    public function getNextFlash(?int $userId = null): ?array
    {
        $pprmFlash = $this->flashRepository->getNextFlash();
        $this->ppRoundMatchFindService->enrich($pprmFlash, true, $userId);
        return $pprmFlash;
    }

    public function getCurrentFlash(?int $userId = null): ?array
    {
        $pprmFlash = $this->flashRepository->getCurrentFlash();
        $this->ppRoundMatchFindService->enrich($pprmFlash, true, $userId);
        return $pprmFlash;
    }
}
