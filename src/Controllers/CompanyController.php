<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class CompanyController extends Controller {
    public function index() {
        $this->authRequired(['admin', 'employee']);
        
        $db = Database::getInstance();
        $companies = $db->query("SELECT * FROM companies ORDER BY trade_name ASC")->fetchAll();
        
        $msg = $_SESSION['msg'] ?? null;
        unset($_SESSION['msg']);

        $this->view('admin/companies/index', ['companies' => $companies, 'msg' => $msg]);
    }

    public function create() {
        $this->authRequired(['admin', 'employee']);
        $this->view('admin/companies/create');
    }

    public function store() {
        $this->authRequired(['admin', 'employee']);
        if ($this->isPost()) {
            $db = Database::getInstance();
            
            $corporate_name = trim($_POST['corporate_name'] ?? '');
            $trade_name = trim($_POST['trade_name'] ?? '');
            $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj'] ?? '');
            $manager_name = trim($_POST['manager_name'] ?? '');
            $main_phone = $_POST['main_phone'] ?? '';
            $has_whatsapp = isset($_POST['has_whatsapp']) ? 1 : 0;
            $email = $_POST['email'] ?? null;
            $address = trim($_POST['address'] ?? '');

            if (empty($corporate_name) || empty($cnpj)) {
                $_SESSION['msg'] = 'Razão Social e CNPJ são obrigatórios.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/companies/create');
            }

            try {
                $stmt = $db->prepare("
                    INSERT INTO companies (corporate_name, trade_name, cnpj, manager_name, main_phone, has_whatsapp, email, address)
                    VALUES (:corporate_name, :trade_name, :cnpj, :manager_name, :main_phone, :has_whatsapp, :email, :address)
                ");
                $stmt->execute([
                    'corporate_name' => $corporate_name,
                    'trade_name' => $trade_name,
                    'cnpj' => $cnpj,
                    'manager_name' => $manager_name,
                    'main_phone' => $main_phone,
                    'has_whatsapp' => $has_whatsapp,
                    'email' => $email,
                    'address' => $address
                ]);

                $company_id = $db->lastInsertId();

                // Criar usuário portal empresa (senha = cnpj limpo)
                if (!empty($email)) {
                    $password = password_hash($cnpj, PASSWORD_DEFAULT);
                    $stmtUser = $db->prepare("
                        INSERT INTO users (name, email, password, role, company_id) 
                        VALUES (:name, :email, :password, 'company', :company_id)
                    ");
                    $stmtUser->execute([
                        'name' => $trade_name ?: $corporate_name,
                        'email' => $email,
                        'password' => $password,
                        'company_id' => $company_id
                    ]);
                }

                $_SESSION['msg'] = 'Empresa cadastrada com sucesso!';
                $this->redirect('/admin/companies');
            } catch (\PDOException $e) {
                $_SESSION['msg'] = 'Erro ao salvar. Verifique se o CNPJ já está cadastrado.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/companies/create');
            }
        }
    }

    public function edit($id) {
        $this->authRequired(['admin', 'employee']);
        
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $company = $stmt->fetch();

        if (!$company) {
            $_SESSION['msg'] = 'Empresa não encontrada.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('/admin/companies');
        }

        $this->view('admin/companies/edit', ['company' => $company]);
    }

    public function update($id) {
        $this->authRequired(['admin', 'employee']);
        
        if ($this->isPost()) {
            $db = Database::getInstance();
            
            $corporate_name = trim($_POST['corporate_name'] ?? '');
            $trade_name = trim($_POST['trade_name'] ?? '');
            $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj'] ?? '');
            $manager_name = trim($_POST['manager_name'] ?? '');
            $main_phone = $_POST['main_phone'] ?? '';
            $has_whatsapp = isset($_POST['has_whatsapp']) ? 1 : 0;
            $email = $_POST['email'] ?? null;
            $address = trim($_POST['address'] ?? '');
            $status = $_POST['status'] ?? 'active';

            if (empty($corporate_name) || empty($cnpj)) {
                $_SESSION['msg'] = 'Razão Social e CNPJ são obrigatórios.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/companies/edit/' . $id);
            }

            try {
                $stmt = $db->prepare("
                    UPDATE companies SET 
                        corporate_name = :corporate_name, 
                        trade_name = :trade_name, 
                        cnpj = :cnpj, 
                        manager_name = :manager_name, 
                        main_phone = :main_phone, 
                        has_whatsapp = :has_whatsapp, 
                        email = :email, 
                        address = :address,
                        status = :status
                    WHERE id = :id
                ");
                $stmt->execute([
                    'corporate_name' => $corporate_name,
                    'trade_name' => $trade_name,
                    'cnpj' => $cnpj,
                    'manager_name' => $manager_name,
                    'main_phone' => $main_phone,
                    'has_whatsapp' => $has_whatsapp,
                    'email' => $email,
                    'address' => $address,
                    'status' => $status,
                    'id' => $id
                ]);

                // Update portal user if email changed
                if (!empty($email)) {
                    // Check if user exists
                    $stmtUser = $db->prepare("SELECT id FROM users WHERE company_id = :id AND role = 'company'");
                    $stmtUser->execute(['id' => $id]);
                    $userExists = $stmtUser->fetch();
                    
                    if ($userExists) {
                        $stmtUpdateUser = $db->prepare("UPDATE users SET email = :email, name = :name WHERE company_id = :id");
                        $stmtUpdateUser->execute([
                            'email' => $email,
                            'name' => $trade_name ?: $corporate_name,
                            'id' => $id
                        ]);
                    } else {
                        // Create user if they added an email later
                        $password = password_hash($cnpj, PASSWORD_DEFAULT);
                        $stmtInsertUser = $db->prepare("
                            INSERT INTO users (name, email, password, role, company_id) 
                            VALUES (:name, :email, :password, 'company', :company_id)
                        ");
                        $stmtInsertUser->execute([
                            'name' => $trade_name ?: $corporate_name,
                            'email' => $email,
                            'password' => $password,
                            'company_id' => $id
                        ]);
                    }
                }

                $_SESSION['msg'] = 'Empresa atualizada com sucesso!';
                $_SESSION['msg_type'] = 'success';
                $this->redirect('/admin/companies');
            } catch (\PDOException $e) {
                $_SESSION['msg'] = 'Erro ao atualizar. Verifique se o CNPJ/Email já pertence a outra empresa.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/companies/edit/' . $id);
            }
        }
    }
}
