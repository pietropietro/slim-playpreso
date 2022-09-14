<?php

declare(strict_types=1);

namespace App\Service\Score;

use App\Service\BaseService;

final class Calculate extends BaseService
{

    public function __construct() {}

    public int $ggngScore = 2;
    public int $uo25Score = 2;
    public int $unoX2Score = 5;
    public int $presoScore = 10;

    public function calculate(int $scoreHome, int $scoreAway, ?int $guessHome, ?int $guessAway){
        $ggNG = $uo25 = $unoX2 = $preso = $score = null;

        if(!is_null($guessHome)){
            $score = 0;
            $realGGNG = $scoreHome > 0 && $scoreAway > 0; 
            $guessGGNG = $guessHome > 0 && $guessAway > 0;
            if($ggNG = $realGGNG === $guessGGNG) $score += $this->ggngScore;

            $realUO25 = $scoreHome + $scoreAway > 2;
            $guessUO25 = $guessHome + $guessAway > 2;
            if($uo25 = $realUO25 === $guessUO25) $score += $this->uo25Score;

            $scoreHome = $scoreHome > $_SERVER['MAX_GUESS_GOALS'] ? $_SERVER['MAX_GUESS_GOALS'] : $scoreHome;
            $scoreAway = $scoreAway > $_SERVER['MAX_GUESS_GOALS'] ? $_SERVER['MAX_GUESS_GOALS'] : $scoreAway;
            
            $realScore = $scoreHome - $scoreAway;
            $guessScore = $guessHome - $guessAway;
            if($unoX2 = $realScore * $guessScore >= 0) $score += $this->unoX2Score;

            if($preso = $scoreHome === $guessHome && $scoreAway === $guessAway)$score += $this->presoScore;
        }
        
        $data = array(
            "unox2" => $unoX2,
            "uo25" => $uo25,
            "ggng" => $ggNG,
            "preso" => $preso,
            "score" => $score
        );

        return $data;
    }
}