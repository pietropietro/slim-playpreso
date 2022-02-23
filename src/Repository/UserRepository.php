<?php

declare(strict_types=1);

namespace App\Repository;

final class UserRepository extends BaseRepository
{
    public function getUser(int $userId)
    {
        $this->getDb()->where('id',$userId);
        //only retrieve certain columns of user. in order to give back a JSON without password and so forth
        $columns = Array ('username','created_at','points','id');
        $user = $this->getDb()->getOne('users', $columns);
        echo("user:");
        print_r($user);
        echo("username:".$user->getUsername());

        if (! $user) {
            throw new \App\Exception\User('User not found.', 404);
        }                           
        return $user;
    }
   
}
