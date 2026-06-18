<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class LogController extends Controller {
    public function index() {
        $this->authRequired(['admin']); // Apenas admin pode ver logs da API
        
        $db = Database::getInstance();
        $logs = $db->query("
            SELECT l.*, e.protocol_code, p.full_name as patient_name, c.trade_name as company_name, u.name as sent_by_name
            FROM message_logs l
            JOIN exams e ON l.exam_id = e.id
            LEFT JOIN patients p ON l.patient_id = p.id
            LEFT JOIN companies c ON l.company_id = c.id
            LEFT JOIN users u ON l.sent_by = u.id
            ORDER BY l.sent_at DESC
        ")->fetchAll();
        
        $this->view('admin/logs/index', ['logs' => $logs]);
    }
}
