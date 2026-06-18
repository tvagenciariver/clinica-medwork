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
}
