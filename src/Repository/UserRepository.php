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

        if (! $user) {
            throw new \App\Exception\User('User not found.', 404);
        }                           
        return $user;
    }

    public function loginUser(string $username, string $password)
    {
        $this->getDb()->where('username',strtolower($username));
        if(!$user=$this->getDb()->getOne('users')){
            throw new \App\Exception\User('Login failed: username or password incorrect.', 400);
        }
        $decodedHash=base64_decode($user['password']);
        if(password_verify($password, $decodedHash)){
            return $user;
        }
        throw new \App\Exception\User('Login failed: username or password incorrect.', 400);
    }
   
}
