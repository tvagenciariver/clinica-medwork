<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class AuthController extends Controller {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Se já estiver logado, redireciona pro lugar certo
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole($_SESSION['role']);
        }
        
        $error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['error']);
        
        $this->view('admin/login', ['error' => $error]);
    }

    public function login() {
        if ($this->isPost()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Preencha todos os campos.';
                $this->redirect('/login');
            }

            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login sucesso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['company_id'] = $user['company_id'];
                $_SESSION['patient_id'] = $user['patient_id'];

                // Atualizar último acesso
                $update = $db->prepare("UPDATE users SET last_access = NOW() WHERE id = :id");
                $update->execute(['id' => $user['id']]);

                $this->redirectBasedOnRole($user['role']);
            } else {
                $_SESSION['error'] = 'Credenciais inválidas ou usuário inativo.';
                $this->redirect('/login');
            }
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        $this->redirect('/login');
    }

    private function redirectBasedOnRole($role) {
        switch ($role) {
            case 'admin':
            case 'employee':
                $this->redirect('/admin/dashboard');
                break;
            case 'patient':
                $this->redirect('/patient/dashboard');
                break;
            case 'company':
                $this->redirect('/company/dashboard');
                break;
            default:
                $this->redirect('/login');
        }
    }
}
