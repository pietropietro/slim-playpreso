<?php

declare(strict_types=1);

namespace App\Service\PPDex;
use App\Service\BaseService;
use App\Service\RedisService;
use App\Repository\PPDexRepository;
use App\Service\PPTournamentType;

final class Find  extends BaseService {

    public function __construct(
        protected RedisService $redisService,
        protected PPDexRepository $ppDexRepository,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
    ){}

    public function getUserPPDex(int $userId): array
    {
        $ppDex = [
            'ppCups' => [],
            'ppLeagues' => []
        ];

        // Helper function to fetch and populate tournament types
        $fetchAndPopulateTypes = function (bool $onlyCups) use (&$ppDex) {
            $tournamentTypes = $this->ppTournamentTypeFindService->get(null, $onlyCups, false, !$onlyCups);
            foreach ($tournamentTypes as $tournamentType) {
                if ($tournamentType['name'] !== 'MOTD') {
                    $key = $onlyCups ? 'ppCups' : 'ppLeagues';
                    if (!isset($ppDex[$key][$tournamentType['name']])) {
                        $ppDex[$key][$tournamentType['name']] = [];
                    }
                    $ppDex[$key][$tournamentType['name']][] = [
                        'ppTournamentType' => [
                            'id' => $tournamentType['id'],
                            'name' => $tournamentType['name'],
                            'level' => $tournamentType['level'],
                            'emoji' => $tournamentType['emoji'],
                            'is_cup' => $onlyCups,
                        ],
                        'userParticipation' => null
                    ];
                }
            }
        };

        // Fetch and populate tournament types for leagues and cups
        $fetchAndPopulateTypes(true);  // For Cups
        $fetchAndPopulateTypes(false); // For Leagues

        // Fetch user participations for leagues
        $leagueParticipations = $this->ppDexRepository->getUserSchemaPPLeagues($userId);
        foreach ($leagueParticipations as $participation) {
            $name = $participation['pptt_name'];
            foreach ($ppDex['ppLeagues'][$name] as &$tournament) {
                if ($tournament['ppTournamentType']['id'] === $participation['pptt_id']) {
                    $tournament['userParticipation'] = [
                        'user_id' => $participation['up_user_id'],
                        'id' => $participation['up_id'],
                        'ppLeague_id' => $participation['up_ppLeague_id'],
                        'updated_at' => $participation['up_updated_at'],
                        'tot_points' => $participation['up_tot_points'],
                        'position' => $participation['up_position'],
                        'started_at' => $participation['up_started_at'],
                        'finished_at' => $participation['up_finished_at'],
                        'is_live' => isset($participation['up_started_at']) && is_null($participation['up_finished_at']),
                    ];
                }
            }
        }

        // Fetch user participations for cups
        $cupParticipations = $this->ppDexRepository->getUserSchemaPPCups($userId);
        foreach ($cupParticipations as $participation) {
            $name = $participation['pptt_name'];
            foreach ($ppDex['ppCups'][$name] as &$tournament) {
                if ($tournament['ppTournamentType']['id'] === $participation['pptt_id']) {
                    $cupFormat = json_decode($participation['pptt_cup_format'], true);
                    $levelIndex = $participation['up_cup_level'] - 1;
                    $reachedLevelString = isset($cupFormat[$levelIndex]) ? $cupFormat[$levelIndex]['name'] : null;

                    $tournament['userParticipation'] = [
                        'user_id' => $participation['up_user_id'],
                        'id' => $participation['up_id'],
                        'ppCup_id' => $participation['up_ppCup_id'],
                        'ppCupGroup_id' => $participation['up_ppCupGroup_id'],
                        'updated_at' => $participation['up_updated_at'],
                        'tot_points' => $participation['up_tot_points'],
                        'position' => $participation['up_position'],
                        'started_at' => $participation['up_started_at'],
                        'finished_at' => $participation['up_finished_at'],
                        'is_live' => isset($participation['up_started_at']) && is_null($participation['up_finished_at']),
                        'reached_level' => $participation['up_cup_level'],
                        'reached_level_string' => $reachedLevelString,
                    ];
                }
            }
        }

        return $ppDex;
    }

}
