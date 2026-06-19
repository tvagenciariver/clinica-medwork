<?php
namespace Controllers;

use Core\Controller;
use Core\Database;
use Services\WahaApiService;

class AppointmentController extends Controller {

    public function index() {
        $this->authRequired(['admin']);
        
        $db = Database::getInstance();
        $appointments = $db->query("
            SELECT a.*, p.full_name as patient_name, p.main_phone, p.has_whatsapp
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            ORDER BY a.appointment_date DESC, a.appointment_time ASC
        ")->fetchAll();

        $msg = $_SESSION['msg'] ?? null;
        $msg_type = $_SESSION['msg_type'] ?? null;
        unset($_SESSION['msg'], $_SESSION['msg_type']);

        $this->view('admin/appointments/index', [
            'appointments' => $appointments,
            'msg' => $msg,
            'msg_type' => $msg_type
        ]);
    }

    public function create() {
        $this->authRequired(['admin']);
        
        $db = Database::getInstance();
        $patients = $db->query("SELECT id, full_name, main_phone FROM patients ORDER BY full_name ASC")->fetchAll();

        $this->view('admin/appointments/create', [
            'patients' => $patients
        ]);
    }

    public function store() {
        $this->authRequired(['admin']);
        
        if ($this->isPost()) {
            $patient_id = $_POST['patient_id'] ?? null;
            $procedure_name = $_POST['procedure_name'] ?? '';
            $appointment_date = $_POST['appointment_date'] ?? '';
            $appointment_time = $_POST['appointment_time'] ?? '';
            
            if (!$patient_id || !$procedure_name || !$appointment_date || !$appointment_time) {
                $_SESSION['msg'] = 'Todos os campos são obrigatórios.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/appointments/create');
            }

            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    INSERT INTO appointments (patient_id, procedure_name, appointment_date, appointment_time, status)
                    VALUES (:patient_id, :procedure_name, :appointment_date, :appointment_time, 'agendado')
                ");
                $stmt->execute([
                    'patient_id' => $patient_id,
                    'procedure_name' => $procedure_name,
                    'appointment_date' => $appointment_date,
                    'appointment_time' => $appointment_time
                ]);
                
                $_SESSION['msg'] = 'Agendamento criado com sucesso!';
                $_SESSION['msg_type'] = 'success';
            } catch (\PDOException $e) {
                $_SESSION['msg'] = 'Erro ao criar agendamento: ' . $e->getMessage();
                $_SESSION['msg_type'] = 'error';
            }
            
            $this->redirect('/admin/appointments');
        }
    }

    public function edit($id) {
        $this->authRequired(['admin']);
        
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $appointment = $stmt->fetch();
        
        if (!$appointment) {
            $_SESSION['msg'] = 'Agendamento não encontrado.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('/admin/appointments');
        }

        $patients = $db->query("SELECT id, full_name, main_phone FROM patients ORDER BY full_name ASC")->fetchAll();

        $this->view('admin/appointments/edit', [
            'appointment' => $appointment,
            'patients' => $patients
        ]);
    }

    public function update($id) {
        $this->authRequired(['admin']);
        
        if ($this->isPost()) {
            $patient_id = $_POST['patient_id'] ?? null;
            $procedure_name = $_POST['procedure_name'] ?? '';
            $appointment_date = $_POST['appointment_date'] ?? '';
            $appointment_time = $_POST['appointment_time'] ?? '';
            $status = $_POST['status'] ?? 'agendado';
            
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    UPDATE appointments 
                    SET patient_id = :patient_id, 
                        procedure_name = :procedure_name, 
                        appointment_date = :appointment_date, 
                        appointment_time = :appointment_time, 
                        status = :status
                    WHERE id = :id
                ");
                $stmt->execute([
                    'patient_id' => $patient_id,
                    'procedure_name' => $procedure_name,
                    'appointment_date' => $appointment_date,
                    'appointment_time' => $appointment_time,
                    'status' => $status,
                    'id' => $id
                ]);
                
                $_SESSION['msg'] = 'Agendamento atualizado com sucesso!';
                $_SESSION['msg_type'] = 'success';
            } catch (\PDOException $e) {
                $_SESSION['msg'] = 'Erro ao atualizar agendamento: ' . $e->getMessage();
                $_SESSION['msg_type'] = 'error';
            }
            
            $this->redirect('/admin/appointments');
        }
    }

    public function getTomorrowIds() {
        $this->authRequired(['admin']);
        
        $db = Database::getInstance();
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $stmt = $db->prepare("
            SELECT a.id, p.has_whatsapp 
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.appointment_date = :date AND a.status = 'agendado'
        ");
        $stmt->execute(['date' => $tomorrow]);
        $appointments = $stmt->fetchAll();
        
        $validIds = [];
        foreach ($appointments as $appt) {
            if ($appt['has_whatsapp']) {
                $validIds[] = $appt['id'];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['ids' => $validIds]);
        exit;
    }

    public function sendSingle() {
        $this->authRequired(['admin']);
        
        if ($this->isPost()) {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID não fornecido']);
                exit;
            }
            
            $result = WahaApiService::sendAppointmentConfirmation($id);
            
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
    }
}
