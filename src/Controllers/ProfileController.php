<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class ProfileController extends Controller {

    public function changePassword() {
        // Exige login, mas não verifica a role para permitir todos
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        $this->view('shared/change_password', [
            'error' => $error,
            'success' => $success
        ]);
    }

    public function updatePassword() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        if ($this->isPost()) {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($password) || empty($confirm_password)) {
                $_SESSION['error'] = 'Preencha todos os campos.';
                $this->redirect('/portal/change-password');
                return;
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = 'As senhas não conferem.';
                $this->redirect('/portal/change-password');
                return;
            }

            if (strlen($password) < 6) {
                $_SESSION['error'] = 'A senha deve ter pelo menos 6 caracteres.';
                $this->redirect('/portal/change-password');
                return;
            }

            try {
                $db = Database::getInstance();
                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("UPDATE users SET password = ?, force_password_change = 0 WHERE id = ?");
                $stmt->execute([$hash, $_SESSION['user_id']]);

                $_SESSION['force_password_change'] = 0;
                $_SESSION['success'] = 'Senha atualizada com sucesso!';
                
                // Redireciona com base na role
                switch ($_SESSION['role']) {
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
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Erro ao atualizar a senha. Tente novamente.';
                $this->redirect('/portal/change-password');
            }
        }
    }
}
