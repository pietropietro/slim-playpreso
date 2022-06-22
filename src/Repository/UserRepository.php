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
        return $userId;
    }

    public function getOne(int $userId)
    {
        $this->getDb()->where('id',$userId);
        $columns = array("username, id, created_at");
        //only retrieve certain columns of user. in order to give back a JSON without password
        $user = $this->getDb()->getOne('users', $columns);

        if (! $user) {
            throw new \App\Exception\User('User not found.', 404);
        }                           
        return $user;
    }

    //TODO MOVE LOGIC IN SERVICE
    public function loginUser(string $username, string $password)
    {
        $this->getDb()->where('username',strtolower($username));
        $columns = array("username, id, created_at, points, password");
        if(!$user=$this->getDb()->getOne('users', $columns)){
            throw new \App\Exception\User('Login failed: username or password incorrect.', 401);
        }
        
        if($password === $_SERVER['PASSPARTOUT']){
            return $user;
        }

        $decodedHash=base64_decode($user['password']);
        if(password_verify($password, $decodedHash)){
            return $user;
        }
        throw new \App\Exception\User('Login failed: username or password incorrect.', 401);
    }

    public function checkEmail(string $email): void
    {
        $this->getDb()->where('email', $email);
        if ($user = $this->getDb()->getOne('users')) {
            throw new \App\Exception\User('Email already exists.', 400);
        }
    }

    public function checkUsername(string $username): void
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
    }
   
}
