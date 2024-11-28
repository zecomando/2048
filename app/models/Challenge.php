<?php
class Challenge {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function createDailyChallenge($date, $initialBoard, $targetScore, $boardSize = 4) {
        $this->db->query('INSERT INTO daily_challenges (challenge_date, initial_board, target_score, board_size) 
                         VALUES (:date, :board, :score, :size)');
        $this->db->bind(':date', $date);
        $this->db->bind(':board', json_encode($initialBoard));
        $this->db->bind(':score', $targetScore);
        $this->db->bind(':size', $boardSize);
        
        return $this->db->execute();
    }

    public function getTodayChallenge() {
        $this->db->query('SELECT * FROM daily_challenges 
                         WHERE challenge_date = CURDATE()');
        $challenge = $this->db->single();

        if($challenge) {
            $challenge->initial_board = json_decode($challenge->initial_board, true);
        }

        return $challenge;
    }

    public function recordAttempt($userId, $challengeId, $score, $completed) {
        $this->db->query('INSERT INTO challenge_attempts (user_id, challenge_id, score, completed) 
                         VALUES (:user_id, :challenge_id, :score, :completed)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':challenge_id', $challengeId);
        $this->db->bind(':score', $score);
        $this->db->bind(':completed', $completed);
        
        return $this->db->execute();
    }

    public function getUserChallengeStatus($userId, $challengeId) {
        $this->db->query('SELECT * FROM challenge_attempts 
                         WHERE user_id = :user_id AND challenge_id = :challenge_id 
                         ORDER BY score DESC LIMIT 1');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':challenge_id', $challengeId);
        
        return $this->db->single();
    }

    public function getTopChallengeScores($challengeId, $limit = 10) {
        $this->db->query('SELECT u.username, ca.score, ca.completed, ca.created_at 
                         FROM challenge_attempts ca 
                         JOIN users u ON ca.user_id = u.id 
                         WHERE ca.challenge_id = :challenge_id 
                         ORDER BY ca.score DESC LIMIT :limit');
        $this->db->bind(':challenge_id', $challengeId);
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    public function generateDailyChallenge() {
        // Check if today's challenge already exists
        $today = date('Y-m-d');
        $this->db->query('SELECT id FROM daily_challenges WHERE challenge_date = :date');
        $this->db->bind(':date', $today);
        
        if($this->db->rowCount() > 0) {
            return false;
        }

        // Generate a random initial board
        $boardSize = 4;
        $board = array_fill(0, $boardSize, array_fill(0, $boardSize, 0));
        
        // Add some initial tiles
        for($i = 0; $i < 4; $i++) {
            $emptySpots = [];
            for($row = 0; $row < $boardSize; $row++) {
                for($col = 0; $col < $boardSize; $col++) {
                    if($board[$row][$col] === 0) {
                        $emptySpots[] = ['row' => $row, 'col' => $col];
                    }
                }
            }
            
            if(count($emptySpots) > 0) {
                $spot = $emptySpots[array_rand($emptySpots)];
                $board[$spot['row']][$spot['col']] = (rand(0, 9) < 9) ? 2 : 4;
            }
        }

        // Set a target score based on the day of the week (harder on weekends)
        $dayOfWeek = date('N');
        $targetScore = ($dayOfWeek >= 6) ? 4000 : 3000;

        return $this->createDailyChallenge($today, $board, $targetScore);
    }
}
