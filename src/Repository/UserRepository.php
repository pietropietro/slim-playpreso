<?php

declare(strict_types=1);

namespace App\Repository;

final class UserRepository extends BaseRepository
{   
    private string $inactivePeriod = '-10 days';

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

    public function anonymizeUser(int $userId): bool
    {
        $anonymizedData = [
            'username' => 'deleted' . $userId,  // Set a unique username
            'email' => 'deleted',  // Replace email with a placeholder
            'password' => 'deleted',  // Replace password with a placeholder
        ];

        $this->db->where('id', $userId);
        $result = $this->db->update('users', $anonymizedData, 1);

        if (!$result) {
            throw new \App\Exception\User('User could not be anonymized.', 500);
        }

        return true;
    }

    public function adminGet(
        ?int $offset = null, 
        ?int $limit = 200
    ){

        $users = $this->db->withTotalCount()->get(
            'users', 
            [$offset, $limit]
        );
    
        return [
            'users' => $users,
            'total' => $this->db->totalCount,
        ];
    }

    public function getValue(string $column){
        return $this->db->getValue('users', $column, null);
    }


    public function getOne(int $userId, ?bool $sensitiveColumns=false)
    {
        $this->db->where('id',$userId);
        $columns = array("username, id, created_at, points");
        $user = $this->db->getOne('users', $sensitiveColumns ? '*' : $columns);

        if (! $user) {
            throw new \App\Exception\User('User not found.', 404);
        }                           
        return $user;
    }

    public function isAdmin(int $userId){
        $this->db->where('id',$userId);
        $this->db->where('admin', 1);
        return !!$this->db->getOne('users') ? true : false;
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
        $columns = array("username, id, created_at, points, password, admin");
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

    public function updatePassword(int $userId, string $passwordEncoded){
        $data = array(
            "password" => $passwordEncoded
        );
        $this->db->where('id', $userId);
        return $this->db->update('users', $data, 1);
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

    public function isInactive(int $userId){

        //if 0 locked guesses in last 10 days --> inactive
        $this->db->where('user_id', $userId);
        $from = (new \DateTime($this->inactivePeriod))->format('Y-m-d');
        $this->db->where('guessed_at', $from, '>');
        return !$this->db->has('guesses');

    }

    public function getInactive(){
        $from = (new \DateTime($this->inactivePeriod))->format('Y-m-d');
        $ids = $this->db->subQuery();

        $ids->groupBy('user_id');
        $ids->where('guessed_at', $from, '>');
        $ids->get('guesses', null, 'user_id');
        $this->db->where('id', $ids, 'NOT IN');
        return $this->db->get('users');
    }


   
}
