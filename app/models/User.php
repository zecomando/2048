<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function register($data) {
        $this->db->query('INSERT INTO users (username, password) VALUES(:username, :password)');
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $data['password']);

        return $this->db->execute();
    }

    public function login($username, $password) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);

        $row = $this->db->single();

        if($row) {
            $hashed_password = $row->password;
            if(password_verify($password, $hashed_password)) {
                return $row;
            }
        }
        return false;
    }

    public function findUserByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);

        $row = $this->db->single();

        return ($this->db->rowCount() > 0);
    }

    public function updatePoints($userId, $points) {
        $this->db->query('UPDATE users SET points = points + :points WHERE id = :id');
        $this->db->bind(':points', $points);
        $this->db->bind(':id', $userId);
        return $this->db->execute();
    }

    public function getUserPoints($userId) {
        $this->db->query('SELECT points FROM users WHERE id = :id');
        $this->db->bind(':id', $userId);
        $row = $this->db->single();
        return $row ? $row->points : 0;
    }
}
