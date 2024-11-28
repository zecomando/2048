<?php
class SavedGame {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function saveGame($userId, $boardState, $score, $boardSize) {
        // Delete previous saved game if exists
        $this->db->query('DELETE FROM saved_games WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        $this->db->execute();

        // Save new game state
        $this->db->query('INSERT INTO saved_games (user_id, board_state, current_score, board_size) 
                         VALUES (:user_id, :board_state, :score, :board_size)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':board_state', json_encode($boardState));
        $this->db->bind(':score', $score);
        $this->db->bind(':board_size', $boardSize);
        
        return $this->db->execute();
    }

    public function loadGame($userId) {
        $this->db->query('SELECT * FROM saved_games WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        $game = $this->db->single();

        if($game) {
            $game->board_state = json_decode($game->board_state, true);
        }

        return $game;
    }

    public function deleteSavedGame($userId) {
        $this->db->query('DELETE FROM saved_games WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }
}
