<?php
namespace Controllers;

use Core\Controller;
use Core\Database;
use Services\WahaApiService;

class ExamController extends Controller {
    
    public function index() {
        $this->authRequired(['admin', 'employee']);
        
        $db = Database::getInstance();

        $search = trim($_GET['search'] ?? '');
        $date_start = $_GET['date_start'] ?? '';
        $date_end = $_GET['date_end'] ?? '';
        $exam_type = trim($_GET['exam_type'] ?? '');
        
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(p.full_name LIKE :search OR p.cpf LIKE :search)";
            $search_clean = preg_replace('/[^0-9]/', '', $search);
            $params['search'] = "%{$search}%";
            if (!empty($search_clean)) {
                $where[count($where)-1] = "(p.full_name LIKE :search OR p.cpf LIKE :search OR p.cpf LIKE :search_clean)";
                $params['search_clean'] = "%{$search_clean}%";
            }
        }
        
        if (!empty($date_start)) {
            $where[] = "e.exam_date >= :date_start";
            $params['date_start'] = $date_start;
        }
        
        if (!empty($date_end)) {
            $where[] = "e.exam_date <= :date_end";
            $params['date_end'] = $date_end;
        }
        
        if (!empty($exam_type)) {
            $where[] = "e.exam_type LIKE :exam_type";
            $params['exam_type'] = "%{$exam_type}%";
        }
        
        $whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        $stmt = $db->prepare("
            SELECT e.*, p.full_name as patient_name, p.cpf, c.trade_name as company_name 
            FROM exams e 
            JOIN patients p ON e.patient_id = p.id 
            LEFT JOIN companies c ON e.company_id = c.id 
            $whereSql
            ORDER BY e.created_at DESC
        ");
        $stmt->execute($params);
        $exams = $stmt->fetchAll();
        
        // Notificações de sessão para exibição
        $msg = $_SESSION['msg'] ?? null;
        unset($_SESSION['msg']);

        $this->view('admin/exams/index', [
            'exams' => $exams, 
            'msg' => $msg,
            'search' => $search,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'exam_type' => $exam_type
        ]);
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
            
            // Tratamento de Upload Múltiplo
            $file_paths = [];
            if (isset($_FILES['exam_files']) && is_array($_FILES['exam_files']['name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $count = count($_FILES['exam_files']['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($_FILES['exam_files']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['exam_files']['name'][$i], PATHINFO_EXTENSION);
                        // Append index to avoid overwriting files with same protocol
                        $fileName = $protocol_code . '_' . ($i + 1) . '.' . $ext;
                        if (move_uploaded_file($_FILES['exam_files']['tmp_name'][$i], $uploadDir . $fileName)) {
                            $file_paths[] = 'uploads/' . $fileName;
                        }
                    }
                }
            }

            // Encode paths to JSON. If empty, null.
            $file_path = !empty($file_paths) ? json_encode($file_paths) : null;

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
