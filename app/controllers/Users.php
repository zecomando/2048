<?php
class Users extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function register() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'username' => trim($_POST['username']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'username_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            if(empty($data['username'])) {
                $data['username_err'] = 'Please enter username';
            } elseif($this->userModel->findUserByUsername($data['username'])) {
                $data['username_err'] = 'Username is already taken';
            }

            if(empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            } elseif(strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            if(empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Please confirm password';
            } else {
                if($data['password'] != $data['confirm_password']) {
                    $data['confirm_password_err'] = 'Passwords do not match';
                }
            }

            if(empty($data['username_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                if($this->userModel->register($data)) {
                    header('location: ' . URLROOT . '/users/login');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('users/register', $data);
            }
        } else {
            $data = [
                'username' => '',
                'password' => '',
                'confirm_password' => '',
                'username_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            $this->view('users/register', $data);
        }
    }

    public function login() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'username' => trim($_POST['username']),
                'password' => trim($_POST['password']),
                'username_err' => '',
                'password_err' => ''
            ];

            if(empty($data['username'])) {
                $data['username_err'] = 'Please enter username';
            }

            if(empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            }

            if($this->userModel->findUserByUsername($data['username'])) {
                // User found
            } else {
                $data['username_err'] = 'No user found';
            }

            if(empty($data['username_err']) && empty($data['password_err'])) {
                $loggedInUser = $this->userModel->login($data['username'], $data['password']);

                if($loggedInUser) {
                    $_SESSION['user_id'] = $loggedInUser->id;
                    $_SESSION['username'] = $loggedInUser->username;
                    header('location: ' . URLROOT . '/pages/game');
                } else {
                    $data['password_err'] = 'Password incorrect';
                    $this->view('users/login', $data);
                }
            } else {
                $this->view('users/login', $data);
            }
        } else {
            $data = [
                'username' => '',
                'password' => '',
                'username_err' => '',
                'password_err' => ''
            ];

            $this->view('users/login', $data);
        }
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        session_destroy();
        header('location: ' . URLROOT . '/users/login');
    }

    public function updatePoints() {
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Not logged in']);
            return;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $score = (int)$_POST['score'];
            $points_earned = floor($score / 100);
            
            if($this->userModel->updatePoints($_SESSION['user_id'], $points_earned)) {
                $newPoints = $this->userModel->getUserPoints($_SESSION['user_id']);
                echo json_encode(['points' => $newPoints]);
            } else {
                echo json_encode(['error' => 'Failed to update points']);
            }
        }
    }
}
