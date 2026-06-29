<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class DashboardController extends Controller {
    public function index() {
        $this->authRequired(['admin', 'employee']);
        
        $db = Database::getInstance();
        
        // Contadores básicos (KPIs)
        $totalExams = $db->query("SELECT COUNT(*) FROM exams")->fetchColumn();
        $totalCompanies = $db->query("SELECT COUNT(*) FROM companies")->fetchColumn();
        $totalPatients = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
        
        $availableExams = $db->query("SELECT COUNT(*) FROM exams WHERE status IN ('available', 'sent_whatsapp', 'viewed_patient', 'viewed_company')")->fetchColumn();
        $pendingExams = $db->query("SELECT COUNT(*) FROM exams WHERE status IN ('registered', 'processing')")->fetchColumn();
        $cancelledExams = $db->query("SELECT COUNT(*) FROM exams WHERE status = 'cancelled'")->fetchColumn();
        
        // Últimos exames
        $recentExams = $db->query("
            SELECT e.id, e.protocol_code, e.exam_type, p.full_name as patient, e.status, e.created_at
            FROM exams e
            JOIN patients p ON e.patient_id = p.id
            ORDER BY e.created_at DESC
            LIMIT 5
        ")->fetchAll();

        $this->view('admin/dashboard', [
            'totalExams' => $totalExams,
            'totalCompanies' => $totalCompanies,
            'totalPatients' => $totalPatients,
            'availableExams' => $availableExams,
            'pendingExams' => $pendingExams,
            'cancelledExams' => $cancelledExams,
            'recentExams' => $recentExams
        ]);
    }
}
