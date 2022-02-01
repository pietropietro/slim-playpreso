<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;

final class UserRepository extends BaseRepository
{
    public function getUser(int $userId)
    {
        $this->getDb()->where('user_id',$userId);
        //only retrieve certain columns of user. in order to give back a JSON without password and so forth
        $columns = Array ('username','created_at','imageurl','user_id');
        $user=$this->getDb()->getOne('users', $columns);
        if (! $user) {
            throw new \App\Exception\User('User not found.', 404);
        }   
        //TODO  add  other data
        /*
            //get guesses in last 3 months
            $guesses = getGuessesForUser($user['user_id']);
            //add guesses to the retrieved json
            $user['guesses'] = $guesses;
            //add presoLeagues
            $user['presoLeagues'] = returnUserPresoLeagues($user['user_id']);
            if($guesses){
                //add TopStats
                // $user['userTopStats'] = getTopStatsForUser($user['user_id']);
                $user['leagueTopStats'] = getTopStats($user['user_id']);
                //add Trophies
                $user['trophies'] = getUserTrophies($user['user_id']);
                //average
                $user['average'] = calculateUserAverage($user['user_id'],20);
            }
        */
                        
        return $user;
    }

    

    // public function checkUserByEmail(string $email): void
    // {
    //     $query = 'SELECT * FROM `users` WHERE `email` = :email';
    //     $statement = $this->database->prepare($query);
    //     $statement->bindParam('email', $email);
    //     $statement->execute();
    //     $user = $statement->fetchObject();
    //     if ($user) {
    //         throw new \App\Exception\User('Email already exists.', 400);
    //     }
    // }
   
}
