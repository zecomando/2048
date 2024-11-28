<?php
class Score {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function addScore($userId, $score) {
        $this->db->query('INSERT INTO scores (user_id, score) VALUES(:user_id, :score)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':score', $score);
        return $this->db->execute();
    }

    public function getHighScores($limit = 10) {
        $this->db->query('SELECT users.username, scores.score, scores.created_at 
                         FROM scores 
                         JOIN users ON scores.user_id = users.id 
                         ORDER BY scores.score DESC 
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getUserHighScore($userId) {
        $this->db->query('SELECT MAX(score) as high_score FROM scores WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        return $row ? $row->high_score : 0;
    }
}
