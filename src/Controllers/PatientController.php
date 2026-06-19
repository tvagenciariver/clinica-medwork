<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class PatientController extends Controller {
    public function index() {
        $this->authRequired(['admin', 'employee']);
        
        $db = Database::getInstance();
        
        $search = trim($_GET['search'] ?? '');
        $where = '';
        $params = [];
        
        if (!empty($search)) {
            $where = "WHERE p.full_name LIKE :search OR p.cpf LIKE :search";
            $search_clean = preg_replace('/[^0-9]/', '', $search); // try numeric for cpf
            $params['search'] = "%{$search}%";
            if (!empty($search_clean)) {
                $where .= " OR p.cpf LIKE :search_clean";
                $params['search_clean'] = "%{$search_clean}%";
            }
        }
        
        $stmt = $db->prepare("
            SELECT p.*, c.trade_name as company_name 
            FROM patients p 
            LEFT JOIN companies c ON p.default_company_id = c.id 
            $where
            ORDER BY p.full_name ASC
        ");
        $stmt->execute($params);
        $patients = $stmt->fetchAll();
        
        $this->view('admin/patients/index', ['patients' => $patients, 'search' => $search]);
    }

    public function create() {
        $this->authRequired(['admin', 'employee']);
        
        $db = Database::getInstance();
        $companies = $db->query("SELECT id, trade_name FROM companies WHERE status = 'active' ORDER BY trade_name ASC")->fetchAll();
        
        $this->view('admin/patients/create', ['companies' => $companies]);
    }

    public function store() {
        $this->authRequired(['admin', 'employee']);
        if ($this->isPost()) {
            $db = Database::getInstance();
            
            // Dados básicos
            $full_name = trim($_POST['full_name'] ?? '');
            $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
            $birth_date_input = trim($_POST['birth_date'] ?? '');
            $birth_date = null;
            if (strlen($birth_date_input) === 10) {
                $parts = explode('/', $birth_date_input);
                if (count($parts) === 3) {
                    $birth_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                }
            }
            
            $main_phone = $_POST['main_phone'] ?? '';
            $has_whatsapp = isset($_POST['has_whatsapp']) ? 1 : 0;
            $email = $_POST['email'] ?? null;
            $default_company_id = !empty($_POST['default_company_id']) ? $_POST['default_company_id'] : null;

            // Insere Paciente
            $stmt = $db->prepare("
                INSERT INTO patients (full_name, cpf, birth_date, main_phone, has_whatsapp, email, default_company_id)
                VALUES (:full_name, :cpf, :birth_date, :main_phone, :has_whatsapp, :email, :default_company_id)
            ");
            
            try {
                $stmt->execute([
                    'full_name' => $full_name,
                    'cpf' => $cpf,
                    'birth_date' => $birth_date,
                    'main_phone' => $main_phone,
                    'has_whatsapp' => $has_whatsapp,
                    'email' => $email,
                    'default_company_id' => $default_company_id
                ]);
                $patient_id = $db->lastInsertId();

                // Cria usuário para o paciente acessar o portal
                if (!empty($email)) {
                    $password = password_hash($cpf, PASSWORD_DEFAULT); // Senha padrão = CPF
                    $stmtUser = $db->prepare("
                        INSERT INTO users (name, email, password, role, patient_id) 
                        VALUES (:name, :email, :password, 'patient', :patient_id)
                    ");
                    $stmtUser->execute([
                        'name' => $full_name,
                        'email' => $email,
                        'password' => $password,
                        'patient_id' => $patient_id
                    ]);
                }

                $this->redirect('/admin/patients');
            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) {
                    $_SESSION['msg'] = 'Este CPF já está cadastrado no sistema.';
                } else {
                    $_SESSION['msg'] = 'Ocorreu um erro inesperado ao salvar o paciente.';
                }
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/patients/create');
            }
        }
    }
}
