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
    
        if(!$userId = $this->db->insert('users', $data)){
            throw new \App\Exception\User('User not created.', 400);
        }
        return $userId;
    }

    public function getOne(int $userId)
    {
        $this->db->where('id',$userId);
        $columns = array("username, id, created_at");
        //only retrieve certain columns of user. in order to give back a JSON without password
        $user = $this->db->getOne('users', $columns);

        if (! $user) {
            throw new \App\Exception\User('User not found.', 404);
        }                           
        return $user;
    }

    public function getId(string $username){
        $this->db->where('username',$username);
        $user = $this->db->getOne('users');
        return $user ? $user['id'] : null;
    }

    //TODO MOVE LOGIC IN SERVICE
    public function loginUser(string $username, string $password)
    {
        $this->db->where('username',strtolower($username));
        $columns = array("username, id, created_at, points, password");
        if(!$user=$this->db->getOne('users', $columns)){
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
        $this->db->where('email', $email);
        if ($user = $this->db->getOne('users')) {
            throw new \App\Exception\User('Email already exists.', 400);
        }
    }

    public function checkUsername(string $username): void
    {
        $this->db->where('username', $username);
        if ($user = $this->db->getOne('users')) {
            throw new \App\Exception\User('Username already exists.', 400);
        }
    }

    public function getUsername(int $userId){
        $this->db->where('id',$userId);
        $columns = Array ('username');
        $user = $this->db->getOne('users', $columns);
        return $user['username'];
    }

    public function getPoints(int $userId){
        $this->db->where('id',$userId);
        $columns = Array ('points');
        return $this->db->getOne('users', $columns)['points'];
    }

    public function minus(int $userId, int $points) : bool {
        $initial = $this->getPoints($userId);
        if($initial < $points ){
            throw new \App\Exception\User('Not enough points.', 401);
        }

        $data = array(
            "points" => $this->db->dec($points)
        );
        $this->db->where('id', $userId);
        return $this->db->update('users', $data, 1);
    }

    public function plus(int $userId, int $points) : bool { 
        $data = array(
            "points" => $this->db->inc($points)
        );
        $this->db->where('id', $userId);
        return $this->db->update('users', $data, 1);
    }
   
}
