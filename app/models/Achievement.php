<?php
class Achievement {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllAchievements() {
        $this->db->query('SELECT * FROM achievements');
        return $this->db->resultSet();
    }

    public function getUserAchievements($userId) {
        $this->db->query('SELECT a.* FROM achievements a 
                         JOIN user_achievements ua ON a.id = ua.achievement_id 
                         WHERE ua.user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function awardAchievement($userId, $achievementId) {
        // Check if already awarded
        $this->db->query('SELECT * FROM user_achievements 
                         WHERE user_id = :user_id AND achievement_id = :achievement_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':achievement_id', $achievementId);
        
        if($this->db->rowCount() > 0) {
            return false;
        }

        // Get achievement points reward
        $this->db->query('SELECT points_reward FROM achievements WHERE id = :id');
        $this->db->bind(':id', $achievementId);
        $achievement = $this->db->single();

        // Start transaction
        $this->db->query('START TRANSACTION');

        // Award achievement
        $this->db->query('INSERT INTO user_achievements (user_id, achievement_id) 
                         VALUES (:user_id, :achievement_id)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':achievement_id', $achievementId);
        $success = $this->db->execute();

        // Update user points
        if($success) {
            $this->db->query('UPDATE users SET points = points + :points 
                             WHERE id = :user_id');
            $this->db->bind(':points', $achievement->points_reward);
            $this->db->bind(':user_id', $userId);
            $success = $this->db->execute();
        }

        if($success) {
            $this->db->query('COMMIT');
            return true;
        } else {
            $this->db->query('ROLLBACK');
            return false;
        }
    }

    public function checkAchievements($userId, $gameData) {
        $achievements = [];

        // Check First Win
        if($gameData['won'] && !$this->hasAchievement($userId, 1)) {
            $achievements[] = 1;
        }

        // Check Speed Demon
        if($gameData['won'] && $gameData['time_spent'] < 300 && !$this->hasAchievement($userId, 2)) {
            $achievements[] = 2;
        }

        // Check Efficient Player
        if($gameData['won'] && $gameData['moves_count'] < 1000 && !$this->hasAchievement($userId, 3)) {
            $achievements[] = 3;
        }

        // Check High Scorer
        if($gameData['score'] >= 20000 && !$this->hasAchievement($userId, 6)) {
            $achievements[] = 6;
        }

        foreach($achievements as $achievementId) {
            $this->awardAchievement($userId, $achievementId);
        }

        return $achievements;
    }

    private function hasAchievement($userId, $achievementId) {
        $this->db->query('SELECT * FROM user_achievements 
                         WHERE user_id = :user_id AND achievement_id = :achievement_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':achievement_id', $achievementId);
        return $this->db->rowCount() > 0;
    }
}
