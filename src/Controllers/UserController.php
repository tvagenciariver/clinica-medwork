<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class UserController extends Controller {

    public function index() {
        $this->authRequired(['admin']);
        
        $db = Database::getInstance();
        $users = $db->query("SELECT id, name, email, role, status, created_at FROM users WHERE role IN ('admin', 'employee') ORDER BY name ASC")->fetchAll();

        $msg = $_SESSION['msg'] ?? null;
        $msg_type = $_SESSION['msg_type'] ?? null;
        unset($_SESSION['msg'], $_SESSION['msg_type']);

        $this->view('admin/users/index', [
            'users' => $users,
            'msg' => $msg,
            'msg_type' => $msg_type
        ]);
    }

    public function create() {
        $this->authRequired(['admin']);
        $this->view('admin/users/create');
    }

    public function store() {
        $this->authRequired(['admin']);
        
        if ($this->isPost()) {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'employee';
            $status = $_POST['status'] ?? 'active';

            if (empty($name) || empty($email) || empty($password)) {
                $_SESSION['msg'] = 'Preencha todos os campos obrigatórios.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/users/create');
                return;
            }

            try {
                $db = Database::getInstance();
                
                // Check if email exists
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $_SESSION['msg'] = 'Este e-mail já está em uso.';
                    $_SESSION['msg_type'] = 'error';
                    $this->redirect('/admin/users/create');
                    return;
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hash, $role, $status]);
                
                $_SESSION['msg'] = 'Usuário criado com sucesso!';
                $_SESSION['msg_type'] = 'success';
                $this->redirect('/admin/users');
            } catch (\Exception $e) {
                $_SESSION['msg'] = 'Erro ao criar usuário: ' . $e->getMessage();
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/users/create');
            }
        }
    }

    public function edit($id) {
        $this->authRequired(['admin']);
        
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name, email, role, status FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['msg'] = 'Usuário não encontrado.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('/admin/users');
            return;
        }

        $this->view('admin/users/edit', ['user' => $user]);
    }

    public function update($id) {
        $this->authRequired(['admin']);
        
        if ($this->isPost()) {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? 'employee';
            $status = $_POST['status'] ?? 'active';
            $password = $_POST['password'] ?? '';

            if (empty($name) || empty($email)) {
                $_SESSION['msg'] = 'Nome e E-mail são obrigatórios.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/users/edit/' . $id);
                return;
            }

            try {
                $db = Database::getInstance();
                
                // Check email uniqueness ignoring self
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $id]);
                if ($stmt->fetch()) {
                    $_SESSION['msg'] = 'Este e-mail já está em uso por outro usuário.';
                    $_SESSION['msg_type'] = 'error';
                    $this->redirect('/admin/users/edit/' . $id);
                    return;
                }

                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET name=?, email=?, password=?, role=?, status=? WHERE id=?");
                    $stmt->execute([$name, $email, $hash, $role, $status, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET name=?, email=?, role=?, status=? WHERE id=?");
                    $stmt->execute([$name, $email, $role, $status, $id]);
                }
                
                $_SESSION['msg'] = 'Usuário atualizado com sucesso!';
                $_SESSION['msg_type'] = 'success';
                $this->redirect('/admin/users');
            } catch (\Exception $e) {
                $_SESSION['msg'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/users/edit/' . $id);
            }
        }
    }

    public function delete($id) {
        $this->authRequired(['admin']);
        
        if ($id == $_SESSION['user_id']) {
            $_SESSION['msg'] = 'Você não pode excluir a si mesmo!';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('/admin/users');
            return;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['msg'] = 'Usuário excluído com sucesso.';
            $_SESSION['msg_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['msg'] = 'Erro ao excluir usuário: ' . $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }
        
        $this->redirect('/admin/users');
    }
}
