<?php
namespace Services;

use Core\Database;

class WahaApiService {
    
    public static function sendMessage($examId, $recipientType) {
        $db = Database::getInstance();
        
        // Buscar dados do exame, paciente e empresa
        $exam = $db->prepare("
            SELECT e.*, p.full_name as patient_name, p.main_phone as patient_phone, p.has_whatsapp as patient_wpp,
                   c.trade_name as company_name, c.main_phone as company_phone, c.has_whatsapp as company_wpp
            FROM exams e
            JOIN patients p ON e.patient_id = p.id
            LEFT JOIN companies c ON e.company_id = c.id
            WHERE e.id = :id
        ");
        $exam->execute(['id' => $examId]);
        $examData = $exam->fetch();

        if (!$examData) {
            return ['status' => 'error', 'message' => 'Exame não encontrado.'];
        }

        if ($examData['status'] !== 'available') {
            return ['status' => 'error', 'message' => 'O exame precisa estar com status "Disponível" para enviar mensagem.'];
        }

        // Buscar configurações da WAHA
        $settingsRaw = $db->query("SELECT * FROM settings")->fetchAll();
        $settings = [];
        foreach($settingsRaw as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $wahaUrl = $settings['waha_base_url'] ?? 'http://localhost:3000';
        $wahaKey = $settings['waha_api_key'] ?? '';
        $wahaSession = $settings['waha_session'] ?? 'default';
        $templatePatient = $settings['waha_template_patient'] ?? "Olá, {paciente}. Exames da MedWork disponíveis. Protocolo: {protocolo}.";
        $templateCompany = $settings['waha_template_company'] ?? "Olá. Exames do colaborador {paciente} disponíveis. Protocolo: {protocolo}.";

        $phone = '';
        $messageText = '';
        
        if ($recipientType === 'patient') {
            if (!$examData['patient_wpp']) return ['status' => 'error', 'message' => 'Paciente não possui WhatsApp marcado.'];
            $phone = preg_replace('/[^0-9]/', '', $examData['patient_phone']);
            
            // Substituição no template
            $messageText = str_replace(
                ['{paciente}', '{protocolo}', '{clinica}'],
                [$examData['patient_name'], $examData['protocol_code'], 'MedWork'],
                $templatePatient
            );
        } elseif ($recipientType === 'company') {
            if (!$examData['company_id']) return ['status' => 'error', 'message' => 'Exame não possui empresa vinculada.'];
            if (!$examData['company_wpp']) return ['status' => 'error', 'message' => 'Empresa não possui WhatsApp marcado.'];
            $phone = preg_replace('/[^0-9]/', '', $examData['company_phone']);
            
            // Substituição no template
            $messageText = str_replace(
                ['{paciente}', '{protocolo}', '{clinica}'],
                [$examData['patient_name'], $examData['protocol_code'], 'MedWork'],
                $templateCompany
            );
        }

        if (empty($phone)) {
            return ['status' => 'error', 'message' => 'Telefone inválido.'];
        }

        // Chamada cURL para WAHA API
        $payload = json_encode([
            'chatId' => "55" . $phone . "@c.us", // Assumindo DDI 55 Brasil
            'text' => $messageText,
            'session' => $wahaSession
        ]);

        $ch = curl_init($wahaUrl . '/api/sendText');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Api-Key: ' . $wahaKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $logStatus = ($httpCode == 200 || $httpCode == 201) ? 'success' : 'error';
        
        // Atualizar log
        $stmtLog = $db->prepare("
            INSERT INTO message_logs (exam_id, patient_id, company_id, recipient_type, destination_phone, message_sent, status, api_response, sent_by)
            VALUES (:exam_id, :patient_id, :company_id, :recipient_type, :destination_phone, :message_sent, :status, :api_response, :sent_by)
        ");
        
        $stmtLog->execute([
            'exam_id' => $examId,
            'patient_id' => $recipientType === 'patient' ? $examData['patient_id'] : null,
            'company_id' => $recipientType === 'company' ? $examData['company_id'] : null,
            'recipient_type' => $recipientType,
            'destination_phone' => $phone,
            'message_sent' => $messageText,
            'status' => $logStatus,
            'api_response' => $response,
            'sent_by' => $_SESSION['user_id']
        ]);

        if ($logStatus === 'success') {
            $db->prepare("UPDATE exams SET status = 'sent_whatsapp' WHERE id = ?")->execute([$examId]);
            return ['status' => 'success', 'message' => 'Mensagem enviada com sucesso!'];
        } else {
            return ['status' => 'error', 'message' => 'Erro na API do WhatsApp. Verifique os logs.'];
        }
    }

    public static function sendAppointmentConfirmation($appointmentId) {
        $db = Database::getInstance();
        
        $appt = $db->prepare("
            SELECT a.*, p.full_name as patient_name, p.main_phone as patient_phone, p.has_whatsapp as patient_wpp
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.id = :id
        ");
        $appt->execute(['id' => $appointmentId]);
        $data = $appt->fetch();

        if (!$data) {
            return ['status' => 'error', 'message' => 'Agendamento não encontrado.'];
        }

        if (!$data['patient_wpp']) {
            return ['status' => 'error', 'message' => 'Paciente não possui WhatsApp.'];
        }

        $settingsRaw = $db->query("SELECT * FROM settings")->fetchAll();
        $settings = [];
        foreach($settingsRaw as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $wahaUrl = $settings['waha_base_url'] ?? 'http://localhost:3000';
        $wahaKey = $settings['waha_api_key'] ?? '';
        $wahaSession = $settings['waha_session'] ?? 'default';
        $template = $settings['waha_template_appointment'] ?? "Olá, {paciente}. Você tem uma consulta de {procedimento} agendada para {data} às {hora} na {clinica}. Responda SIM para confirmar ou NÃO para cancelar.";

        $phone = preg_replace('/[^0-9]/', '', $data['patient_phone']);
        
        $messageText = str_replace(
            ['{paciente}', '{procedimento}', '{data}', '{hora}', '{clinica}'],
            [$data['patient_name'], $data['procedure_name'], date('d/m/Y', strtotime($data['appointment_date'])), date('H:i', strtotime($data['appointment_time'])), 'MedWork'],
            $template
        );

        $payload = json_encode([
            'chatId' => "55" . $phone . "@c.us",
            'text' => $messageText,
            'session' => $wahaSession
        ]);

        $ch = curl_init($wahaUrl . '/api/sendText');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Api-Key: ' . $wahaKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $logStatus = ($httpCode == 200 || $httpCode == 201) ? 'success' : 'error';
        
        $stmtLog = $db->prepare("
            INSERT INTO message_logs (patient_id, recipient_type, destination_phone, message_sent, status, api_response, sent_by)
            VALUES (:patient_id, 'patient', :destination_phone, :message_sent, :status, :api_response, :sent_by)
        ");
        
        $stmtLog->execute([
            'patient_id' => $data['patient_id'],
            'destination_phone' => $phone,
            'message_sent' => $messageText,
            'status' => $logStatus,
            'api_response' => $response,
            'sent_by' => $_SESSION['user_id'] ?? null
        ]);

        return ['status' => $logStatus];
    }

    public static function sendDirectMessage($phone, $message) {
        $db = Database::getInstance();
        $settingsRaw = $db->query("SELECT * FROM settings")->fetchAll();
        $settings = [];
        foreach($settingsRaw as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $wahaUrl = $settings['waha_base_url'] ?? 'http://localhost:3000';
        $wahaKey = $settings['waha_api_key'] ?? '';
        $wahaSession = $settings['waha_session'] ?? 'default';

        $payload = json_encode([
            'chatId' => $phone . "@c.us",
            'text' => $message,
            'session' => $wahaSession
        ]);

        $ch = curl_init($wahaUrl . '/api/sendText');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Api-Key: ' . $wahaKey
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}
