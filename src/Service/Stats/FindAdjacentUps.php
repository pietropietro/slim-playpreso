<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\PPTournamentType;
use App\Repository\UserParticipationRepository;

final class FindAdjacentUps extends BaseService{
    public function __construct(
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
    ) {}


    public function getUsersWithMostAdjacentPositions(int $userId, int $year) {
        $userParticipations = $this->userParticipationRepository->getForUser(
            $userId, null, null, true, null, $year.'-01-01', $year.'-12-31'
        );

        if (!$userParticipations) return [];

        $countedAdjacentUsers = [];
        foreach ($userParticipations as &$participation) {
            $adjacentParticipations = $this->userParticipationRepository->findAdjacentParticipants(
                $userId, $participation
            );
            $participation['adjacent'] = $adjacentParticipations;
            $this->countAdjacentOccurrences($countedAdjacentUsers, $adjacentParticipations);
        }

        arsort($countedAdjacentUsers);
        $mostAdjacentUserId = key($countedAdjacentUsers);
    
        $this->filterResults($userParticipations, $mostAdjacentUserId);
        
        return [
            'mostAdjacentUserId' => $mostAdjacentUserId,
            'totalAdjacentCount' => $countedAdjacentUsers[$mostAdjacentUserId],
            'adjacentParticipations' => array_values($userParticipations) // Reindex array
        ];

    }

    private function countAdjacentOccurrences(array &$countedArr, array $adjacentParticipations){
        foreach ($adjacentParticipations as $adjacent) {
            if (!isset($countedArr[$adjacent['user_id']])) {
                $countedArr[$adjacent['user_id']] = 0;
            }
            $countedArr[$adjacent['user_id']]++;
        }
    }

    private function filterResults(array &$userParticipations, int $mostAdjacentUserId){
        // Iterate the user ups and eliminate the adjacent ups where the user_id is not mostAdjacentUserId
        foreach ($userParticipations as &$up) {
            $up['adjacent'] = array_filter($up['adjacent'], function($adj) use ($mostAdjacentUserId) {
                return $adj['user_id'] == $mostAdjacentUserId;
            });
        }
    
        // Eliminate ups where no adjacent so to return only the userParticipations with adjacent
        $userParticipations = array_filter($userParticipations, function($up) {
            return !empty($up['adjacent']);
        });

        // after filtered useless elements, add ptt data 
        foreach ($userParticipations as &$up) {
            $up['ppTournamentType'] = $this->ppTournamentTypeFindService->getOne(
                $up['ppTournamentType_id'], false
            );
        }

    }
}