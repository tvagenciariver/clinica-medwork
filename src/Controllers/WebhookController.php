<?php
namespace Controllers;

use Core\Controller;
use Core\Database;
use Services\WahaApiService;

class WebhookController extends Controller {

    public function wahaReceiver() {
        // Recebe o JSON enviado pela WAHA
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Log básico para debug (opcional, útil para troubleshoot inicial)
        file_put_contents(__DIR__ . '/../../public/waha_webhook.log', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);

        if (!$data || !isset($data['event']) || $data['event'] !== 'message') {
            http_response_code(200); // Retorna 200 pra WAHA não ficar retentando atoa
            echo "OK";
            return;
        }

        $payload = $data['payload'] ?? [];
        $from = $payload['from'] ?? '';
        $body = trim(strtolower($payload['body'] ?? ''));

        // Ignorar mensagens de grupos ou sistema
        if (strpos($from, '@g.us') !== false || empty($from) || empty($body)) {
            http_response_code(200);
            echo "Ignored";
            return;
        }

        // Limpar o número do remetente (remover @c.us e prefixo 55 se houver, pra buscar no banco)
        $cleanPhone = preg_replace('/[^0-9]/', '', $from);
        
        // Em muitos casos o WhatsApp envia 55DDNNNNNNNNN, o banco pode ter o telefone mascarado (DD) 9NNNN-NNNN
        // Então buscaremos pacientes onde o main_phone apenas números "contém" ou "é parecido" com os últimos 10/11 digitos.
        // Como não sabemos a formatação exata do banco, vamos usar uma busca simplificada com LIKE no banco.
        
        $phoneQueryStr = substr($cleanPhone, -8); // pegar os ultimos 8 digitos pra garantir match

        $db = Database::getInstance();
        
        // Procurar paciente que tenha esse telefone
        $stmt = $db->prepare("SELECT id FROM patients WHERE REPLACE(REPLACE(REPLACE(REPLACE(main_phone, ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?");
        $stmt->execute(['%' . $phoneQueryStr]);
        $patients = $stmt->fetchAll();

        if (count($patients) === 0) {
            http_response_code(200);
            echo "Patient not found";
            return;
        }

        // Para evitar conflitos, pegamos o primeiro paciente que bater e procuramos o agendamento mais próximo (maior que hoje-1 ou = amanhã)
        $patientIds = array_column($patients, 'id');
        $placeholders = str_repeat('?,', count($patientIds) - 1) . '?';
        
        // Procurar o agendamento de status "agendado" para amanhã (ou hoje se for de última hora)
        $apptStmt = $db->prepare("
            SELECT id 
            FROM appointments 
            WHERE patient_id IN ($placeholders) 
              AND status = 'agendado' 
              AND appointment_date >= CURDATE()
            ORDER BY appointment_date ASC, appointment_time ASC
            LIMIT 1
        ");
        $apptStmt->execute($patientIds);
        $appointment = $apptStmt->fetch();

        if (!$appointment) {
            http_response_code(200);
            echo "No pending appointments";
            return;
        }

        $appointmentId = $appointment['id'];
        
        // Analisar a resposta (SIM ou NÃO)
        $isSim = in_array($body, ['sim', 's', 'confirmado', 'confirmo', 'vou']);
        $isNao = in_array($body, ['nao', 'não', 'n', 'cancelar', 'cancela', 'não vou']);

        if ($isSim) {
            $db->prepare("UPDATE appointments SET status = 'confirmado' WHERE id = ?")->execute([$appointmentId]);
            // Responder agradecendo
            WahaApiService::sendDirectMessage($cleanPhone, "Ótimo! Sua consulta foi *confirmada*. Aguardamos você.");
        } elseif ($isNao) {
            $db->prepare("UPDATE appointments SET status = 'cancelado' WHERE id = ?")->execute([$appointmentId]);
            // Responder confirmando o cancelamento
            WahaApiService::sendDirectMessage($cleanPhone, "Tudo bem, sua consulta foi *cancelada*. Se precisar, entre em contato para reagendar.");
        }

        http_response_code(200);
        echo "Processed";
    }
}
