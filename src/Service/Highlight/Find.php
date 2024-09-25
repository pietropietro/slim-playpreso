<?php

declare(strict_types=1);

namespace App\Service\Highlight;

use App\Repository\HighlightRepository;
use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\Trophy;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected Trophy\Find $trophyFindService,
        protected HighlightRepository $highlightRepository,
    ){}

    private const REDIS_KEY_HIGHLIGHT = 'highlight';

    public function getLatestTrophies(int $limit = 5){
        $trophies = $this->trophyFindService->getLatestTrophies($limit);
        return $trophies;
    }

    // public function getChart(
    //     ?int $page = 1, 
    //     ?int $limit = 10, 
    // ){
    //     $offset = ($page - 1) * $limit;

    //     if (self::isRedisEnabled() === true ) {
    //         $redisKey = sprintf(self::REDIS_KEY_MOTD_CHART.':%d:limit:%d', $page, $limit);
    //         $cachedchart = $this->redisService->get($redisKey); // This returns null if not found or the user data if found
    //         if ($cachedchart !== null) {
    //             return $cachedchart;
    //         }
    //     } 


    //     $result = $this->motdRepository->retrieveMotdChart( $offset, $limit);

    //     foreach ($result['chart'] as &$chartItem) {
    //         // do the magic (i.e. fill the period and add zeros on missing dates)
    //         $chartItem['sparkline_data'] = $this->fillSparklineData($chartItem);
    //         unset($chartItem['concat_points']);
    //         unset($chartItem['concat_motd']);

    //         $chartItem['guesses'] = $this->motdFindService->getLastForUser($chartItem['user_id']);
    //     }

    //     if (self::isRedisEnabled() === true ) {
    //         $expiration = 12 * 60 * 60; // 12 hours in seconds
    //         $this->redisService->setex($redisKey, $result, $expiration);
    //     }
    //     return $result;
    // }





}