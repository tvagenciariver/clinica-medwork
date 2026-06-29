<?php
namespace Core;

class Controller {
    protected function view($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . "/../Views/{$view}.php";
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View não encontrada: {$view}");
        }
    }
    
    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function authRequired($roles = []) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        // Se precisa trocar senha e não está na rota de troca de senha
        if (!empty($_SESSION['force_password_change']) && strpos($_SERVER['REQUEST_URI'], '/portal/change-password') === false) {
            $this->redirect('/portal/change-password');
        }

        if (!empty($roles) && !in_array($_SESSION['role'], $roles)) {
            die("Acesso negado."); // TODO: Redirecionar para uma página de erro agradável
        }
    }
}
