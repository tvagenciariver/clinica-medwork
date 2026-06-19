<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class SpecialtyController extends Controller {

    public function index() {
        $this->authRequired(['admin', 'employee']);
        $db = Database::getInstance();
        $specialties = $db->query("SELECT * FROM specialties ORDER BY name ASC")->fetchAll();
        $this->view('admin/specialties/index', ['specialties' => $specialties]);
    }

    public function create() {
        $this->authRequired(['admin']);
        $this->view('admin/specialties/create');
    }

    public function store() {
        $this->authRequired(['admin']);
        if ($this->isPost()) {
            $name = trim($_POST['name'] ?? '');
            $color_hex = trim($_POST['color_hex'] ?? '#3b82f6');
            
            if (empty($name)) {
                $_SESSION['msg'] = 'O nome da especialidade é obrigatório.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/specialties/create');
            }

            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO specialties (name, color_hex) VALUES (:name, :color_hex)");
            $stmt->execute(['name' => $name, 'color_hex' => $color_hex]);

            $_SESSION['msg'] = 'Especialidade cadastrada com sucesso.';
            $_SESSION['msg_type'] = 'success';
            $this->redirect('/admin/specialties');
        }
    }

    public function edit($id) {
        $this->authRequired(['admin']);
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM specialties WHERE id = ?");
        $stmt->execute([$id]);
        $specialty = $stmt->fetch();

        if (!$specialty) {
            $_SESSION['msg'] = 'Especialidade não encontrada.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('/admin/specialties');
        }

        $this->view('admin/specialties/edit', ['specialty' => $specialty]);
    }

    public function update($id) {
        $this->authRequired(['admin']);
        if ($this->isPost()) {
            $name = trim($_POST['name'] ?? '');
            $color_hex = trim($_POST['color_hex'] ?? '#3b82f6');

            if (empty($name)) {
                $_SESSION['msg'] = 'O nome da especialidade é obrigatório.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect("/admin/specialties/edit/{$id}");
            }

            $db = Database::getInstance();
            $stmt = $db->prepare("UPDATE specialties SET name = :name, color_hex = :color_hex WHERE id = :id");
            $stmt->execute(['name' => $name, 'color_hex' => $color_hex, 'id' => $id]);

            $_SESSION['msg'] = 'Especialidade atualizada com sucesso.';
            $_SESSION['msg_type'] = 'success';
            $this->redirect('/admin/specialties');
        }
    }

    public function delete($id) {
        $this->authRequired(['admin']);
        $db = Database::getInstance();
        $db->prepare("DELETE FROM specialties WHERE id = ?")->execute([$id]);
        $_SESSION['msg'] = 'Especialidade excluída com sucesso.';
        $_SESSION['msg_type'] = 'success';
        $this->redirect('/admin/specialties');
    }
}
