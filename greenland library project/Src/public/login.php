
<?php
// MVC Controller for login
session_start();
require_once 'config.php';
require_once 'models/User.php';

class LoginController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            $user = $this->userModel->authenticate($username, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid credentials';
            }
        }
        $this->showLoginForm($error ?? '');
    }
    
    private function showLoginForm($error = '') {
        include 'views/login_view.php';
    }
}

$controller = new LoginController();
$controller->login();
?>
