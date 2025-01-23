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
            //gol / no-gol means if both the match teams score at least one game
            //first we check what was the match outcome for gol / no-gol
            $realGGNG = $scoreHome > 0 && $scoreAway > 0; 
            //then we check what was the user prediction for gol / no-gol based on their score prediction
            $guessGGNG = $guessHome > 0 && $guessAway > 0;
            if($ggNG = $realGGNG === $guessGGNG) $points += $this->ggngPoints;

            //under2.5 / over2.5 means if the sum of match goals is < or > than 2.5 goals.
            //first we check what was the match outcome for under / over 2.5
            $realUO25 = $scoreHome + $scoreAway > 2;
            //then what was the user prediction for under / over 2.5
            $guessUO25 = $guessHome + $guessAway > 2;
            if($uo25 = $realUO25 === $guessUO25) $points += $this->uo25Points;

            $maxGoals = (int)$_SERVER['MAX_GUESS_GOALS']; 
            
            //1X2 is if the hometeam won (1), if is a draw (x) or away team won (2)
            $unoX2 = ($scoreHome > $scoreAway && $guessHome > $guessAway) ||
                ($scoreHome < $scoreAway && $guessHome < $guessAway) ||
                ($scoreHome == $scoreAway && $guessHome == $guessAway) ||
                //since in playpreso max predictable amount of goals for a team is 3
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