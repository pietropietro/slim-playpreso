<?php

declare(strict_types=1);

namespace App\Repository;

final class UserRepository extends BaseRepository
{
    public function create($user){
        $data = array(
            'username' => strtolower($user->username),
            'password' => $user->password,
            'email' => strtolower($user->email),
            'country' => $user->country,
            'points' => $_SERVER['STARTING_POINTS']
        );
    
        if(!$userId = $this->getDb()->insert('users', $data)){
            throw new \App\Exception\User('User not created.', 400);
        }
        return $this->getUser($userId);
    }

    public function getUser(int $userId)
    {
        $this->getDb()->where('id',$userId);
        //only retrieve certain columns of user. in order to give back a JSON without password
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
            throw new \App\Exception\User('Login failed: username or password incorrect.', 401);
        }
        $decodedHash=base64_decode($user['password']);
        if(password_verify($password, $decodedHash)){
            return $user;
        }
        throw new \App\Exception\User('Login failed: username or password incorrect.', 401);
    }

    public function checkUserByEmail(string $email): void
    {
        $this->getDb()->where('email', $email);
        if ($user = $this->getDb()->getOne('users')) {
            throw new \App\Exception\User('Email already exists.', 400);
        }
    }

    public function checkUserByUsername(string $username): void
    {
        $this->getDb()->where('username', $username);
        if ($user = $this->getDb()->getOne('users')) {
            throw new \App\Exception\User('Username already exists.', 400);
        }
    }

    public function getUsername(int $userId){
        $this->getDb()->where('id',$userId);
        $columns = Array ('username');
        $user = $this->getDb()->getOne('users', $columns);
        return $user['username'];
    }

    public function getPoints(int $userId){
        $this->getDb()->where('id',$userId);
        $columns = Array ('points');
        return $this->getDb()->getOne('users', $columns)['points'];
    }

    public function minus(int $userId, int $points){
        $initial = $this->getPoints($userId);
        if($initial < $points ){
            throw new \App\Exception\User('Not enough points.', 401);
        }
        $data = array(
            "points" => $initial - $points
        );
        $this->getDb()->where('id', $userId);
        return $this->getDb()->update('users', $data, 1);
        // ->query("UPDATE users SET points = points - $points WHERE id = $userId ");
    }
   
}
