<?php

declare(strict_types=1);

namespace App\Service\Points;

use App\Service\BaseService;

final class Calculate extends BaseService
{

    public function __construct() {}

    public int $ggngPoints = 2;
    public int $uo25Points = 2;
    public int $unoX2Points = 5;
    public int $presoPoints = 10;

    public function calculate(int $scoreHome, int $scoreAway, ?int $guessHome, ?int $guessAway){
        $ggNG = $uo25 = $unoX2 = $preso = $points = null;

        if(!is_null($guessHome)){
            $points = 0;
            $realGGNG = $scoreHome > 0 && $scoreAway > 0; 
            $guessGGNG = $guessHome > 0 && $guessAway > 0;
            if($ggNG = $realGGNG === $guessGGNG) $points += $this->ggngPoints;

            $realUO25 = $scoreHome + $scoreAway > 2;
            $guessUO25 = $guessHome + $guessAway > 2;
            if($uo25 = $realUO25 === $guessUO25) $points += $this->uo25Points;

            $maxGoals = (int)$_SERVER['MAX_GUESS_GOALS']; 
            
            $unoX2 = ($scoreHome > $scoreAway && $guessHome > $guessAway) ||
                ($scoreHome < $scoreAway && $guessHome < $guessAway) ||
                ($scoreHome == $scoreAway && $guessHome == $guessAway) ||
                // if guess is 3+ - 3+ and match is 4-3, 4-4, 3-6 is always correct
                ($scoreHome >= $maxGoals && $scoreAway >= $maxGoals && $guessHome >= $maxGoals && $guessAway >= $maxGoals) 
                ? true : false;
            
            if($unoX2){
                $points += $this->unoX2Points;
            }

            $scoreHome = $scoreHome > $maxGoals ? $maxGoals : $scoreHome;
            $scoreAway = $scoreAway > $maxGoals ? $maxGoals : $scoreAway;

            if($preso = $scoreHome === $guessHome && $scoreAway === $guessAway){
                $points += $this->presoPoints;
            }
        }
        
        $data = array(
            "unox2" => $unoX2,
            "uo25" => $uo25,
            "ggng" => $ggNG,
            "preso" => $preso,
            "points" => $points
        );

        return $data;
    }
}