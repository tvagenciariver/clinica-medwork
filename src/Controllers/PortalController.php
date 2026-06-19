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
        
        // Exames dos funcionários atrelados a esta empresa
        $exams = $db->query("
            SELECT e.*, p.full_name as patient_name, p.cpf
            FROM exams e 
            JOIN patients p ON e.patient_id = p.id
            WHERE e.company_id = $company_id 
            AND e.origin = 'company'
            AND e.status IN ('available', 'sent_whatsapp', 'viewed_patient', 'viewed_company')
            ORDER BY e.exam_date DESC
        ")->fetchAll();
        
        $this->view('company/dashboard', ['exams' => $exams]);
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
