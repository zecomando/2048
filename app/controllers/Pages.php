<?php
class Pages extends Controller {
    private $userModel;
    private $scoreModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->scoreModel = $this->model('Score');
    }

    public function index() {
        if(isset($_SESSION['user_id'])) {
            header('location: ' . URLROOT . '/pages/game');
        }
        
        $data = [
            'title' => SITENAME,
            'description' => 'Play 2048 - Join numbers and get to the 2048 tile!'
        ];
        
        $this->view('pages/index', $data);
    }

    public function game() {
        if(!isset($_SESSION['user_id'])) {
            header('location: ' . URLROOT . '/users/login');
        }

        $data = [
            'title' => 'Play 2048',
            'user_points' => $this->userModel->getUserPoints($_SESSION['user_id']),
            'high_score' => $this->scoreModel->getUserHighScore($_SESSION['user_id']),
            'top_scores' => $this->scoreModel->getHighScores()
        ];

        $this->view('pages/game', $data);
    }

    public function leaderboard() {
        $data = [
            'title' => 'Leaderboard',
            'scores' => $this->scoreModel->getHighScores(20)
        ];

        $this->view('pages/leaderboard', $data);
    }
}
