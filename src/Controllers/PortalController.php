<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class PortalController extends Controller {
    
    public function patient() {
        $this->authRequired(['patient']);
        $patient_id = $_SESSION['patient_id'];
        
        $db = Database::getInstance();
        
        // Exames do paciente disponíveis
        $exams = $db->query("
            SELECT e.*, c.trade_name as company_name 
            FROM exams e 
            LEFT JOIN companies c ON e.company_id = c.id
            WHERE e.patient_id = $patient_id 
            AND e.status IN ('available', 'sent_whatsapp', 'viewed_patient', 'viewed_company')
            ORDER BY e.exam_date DESC
        ")->fetchAll();
        
        $this->view('patient/dashboard', ['exams' => $exams]);
    }

    public function company() {
        $this->authRequired(['company']);
        $company_id = $_SESSION['company_id'];
        
        $db = Database::getInstance();
        
        // Filtros de busca
        $search = trim($_GET['search'] ?? '');
        $date_start = $_GET['date_start'] ?? '';
        $date_end = $_GET['date_end'] ?? '';
        $exam_type = trim($_GET['exam_type'] ?? '');
        
        $where = ["e.company_id = $company_id", "e.origin = 'company'", "e.status IN ('available', 'sent_whatsapp', 'viewed_patient', 'viewed_company')"];
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

        $whereSql = implode(" AND ", $where);

        $stmt = $db->prepare("
            SELECT e.*, p.full_name as patient_name, p.cpf
            FROM exams e 
            JOIN patients p ON e.patient_id = p.id
            WHERE $whereSql
            ORDER BY e.exam_date DESC
        ");
        $stmt->execute($params);
        $exams = $stmt->fetchAll();
        
        $this->view('company/dashboard', [
            'exams' => $exams,
            'search' => $search,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'exam_type' => $exam_type
        ]);
    }

    public function viewExam($id) {
        $this->authRequired(['company', 'patient']);
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT e.*, p.full_name as patient_name FROM exams e JOIN patients p ON e.patient_id = p.id WHERE e.id = :id");
        $stmt->execute(['id' => $id]);
        $exam = $stmt->fetch();

        if (!$exam || empty($exam['file_path'])) {
            die("Exame não encontrado ou arquivo indisponível.");
        }

        // Check permissions
        if ($_SESSION['role'] === 'company' && $exam['company_id'] != $_SESSION['company_id']) {
            die("Acesso negado.");
        }
        if ($_SESSION['role'] === 'patient' && $exam['patient_id'] != $_SESSION['patient_id']) {
            die("Acesso negado.");
        }

        // Update status if it's the first time viewing
        $newStatus = null;
        if ($_SESSION['role'] === 'company' && in_array($exam['status'], ['available', 'sent_whatsapp', 'viewed_patient'])) {
            $newStatus = 'viewed_company';
        } else if ($_SESSION['role'] === 'patient' && in_array($exam['status'], ['available', 'sent_whatsapp'])) {
            $newStatus = 'viewed_patient';
        }

        if ($newStatus) {
            $update = $db->prepare("UPDATE exams SET status = :status WHERE id = :id");
            $update->execute(['status' => $newStatus, 'id' => $id]);
        }

        // Load viewer
        $this->view('portal/exam_view', ['exam' => $exam]);
    }
}
