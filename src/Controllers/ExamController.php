<?php
namespace Controllers;

use Core\Controller;
use Core\Database;
use Services\WahaApiService;

class ExamController extends Controller {
    
    public function index() {
        $this->authRequired(['admin', 'employee']);
        
        $db = Database::getInstance();
        $exams = $db->query("
            SELECT e.*, p.full_name as patient_name, c.trade_name as company_name 
            FROM exams e 
            JOIN patients p ON e.patient_id = p.id 
            LEFT JOIN companies c ON e.company_id = c.id
            ORDER BY e.created_at DESC
        ")->fetchAll();
        
        // Notificações de sessão para exibição
        $msg = $_SESSION['msg'] ?? null;
        unset($_SESSION['msg']);

        $this->view('admin/exams/index', ['exams' => $exams, 'msg' => $msg]);
    }

    public function create() {
        $this->authRequired(['admin', 'employee']);
        $db = Database::getInstance();
        $patients = $db->query("SELECT id, full_name, cpf FROM patients ORDER BY full_name ASC")->fetchAll();
        $companies = $db->query("SELECT id, trade_name FROM companies WHERE status = 'active' ORDER BY trade_name ASC")->fetchAll();
        $this->view('admin/exams/create', ['patients' => $patients, 'companies' => $companies]);
    }

    public function store() {
        $this->authRequired(['admin', 'employee']);
        if ($this->isPost()) {
            $db = Database::getInstance();
            
            $patient_id = $_POST['patient_id'] ?? null;
            $origin = $_POST['origin'] ?? 'private';
            $company_id = ($origin === 'company' && !empty($_POST['company_id'])) ? $_POST['company_id'] : null;
            $exam_type = trim($_POST['exam_type'] ?? '');
            
            $exam_date_input = trim($_POST['exam_date'] ?? '');
            $exam_date = null;
            if (strlen($exam_date_input) === 10) {
                $parts = explode('/', $exam_date_input);
                if (count($parts) === 3) {
                    $exam_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                }
            }

            $responsible_doctor = trim($_POST['responsible_doctor'] ?? '');
            $observations = trim($_POST['observations'] ?? '');
            $allow_whatsapp = isset($_POST['allow_whatsapp']) ? 1 : 0;
            
            // Gerar protocolo unico
            $protocol_code = date('YmdHi') . rand(1000, 9999);
            
            // Tratamento de Upload Simples
            $file_path = null;
            if (isset($_FILES['exam_file']) && $_FILES['exam_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['exam_file']['name'], PATHINFO_EXTENSION);
                $fileName = $protocol_code . '.' . $ext;
                if (move_uploaded_file($_FILES['exam_file']['tmp_name'], $uploadDir . $fileName)) {
                    $file_path = 'uploads/' . $fileName;
                }
            }

            try {
                $stmt = $db->prepare("
                    INSERT INTO exams (patient_id, origin, company_id, exam_type, exam_date, responsible_doctor, file_path, observations, protocol_code, allow_whatsapp, created_by, status)
                    VALUES (:patient_id, :origin, :company_id, :exam_type, :exam_date, :responsible_doctor, :file_path, :observations, :protocol_code, :allow_whatsapp, :created_by, 'registered')
                ");
                $stmt->execute([
                    'patient_id' => $patient_id,
                    'origin' => $origin,
                    'company_id' => $company_id,
                    'exam_type' => $exam_type,
                    'exam_date' => $exam_date,
                    'responsible_doctor' => $responsible_doctor,
                    'file_path' => $file_path,
                    'observations' => $observations,
                    'protocol_code' => $protocol_code,
                    'allow_whatsapp' => $allow_whatsapp,
                    'created_by' => $_SESSION['user_id']
                ]);
                
                $_SESSION['msg'] = 'Exame cadastrado com sucesso! Protocolo: ' . $protocol_code;
                $_SESSION['msg_type'] = 'success';
                $this->redirect('/admin/exams');
            } catch (\PDOException $e) {
                $_SESSION['msg'] = 'Erro ao salvar exame: ' . $e->getMessage();
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/exams/create');
            }
        }
    }

    // Tela de envio WAHA simulada para a rota
    public function sendWaha() {
        $this->authRequired(['admin', 'employee']);
        
        $examId = $_GET['id'] ?? null;
        $target = $_GET['target'] ?? null; // 'patient' or 'company'
        
        if ($examId && $target) {
            $result = WahaApiService::sendMessage($examId, $target);
            $_SESSION['msg'] = $result['message'];
            if($result['status'] === 'error'){
                $_SESSION['msg_type'] = 'error';
            } else {
                $_SESSION['msg_type'] = 'success';
            }
        }
        
        $this->redirect('/admin/exams');
    }

    public function makeAvailable() {
        $this->authRequired(['admin', 'employee']);
        $examId = $_GET['id'] ?? null;
        if($examId){
            $db = Database::getInstance();
            $db->prepare("UPDATE exams SET status = 'available', available_at = NOW() WHERE id = ?")->execute([$examId]);
            $_SESSION['msg'] = 'Exame marcado como Disponível!';
            $_SESSION['msg_type'] = 'success';
        }
        $this->redirect('/admin/exams');
    }
}
