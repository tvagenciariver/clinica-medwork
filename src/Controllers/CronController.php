<?php
namespace Controllers;

use Core\Controller;
use Core\Database;
use Services\WahaApiService;

class CronController extends Controller {

    public function runDailyWaha() {
        // Para cron jobs demorados, ignora aborto do usuário e remove limite de tempo do script
        ignore_user_abort(true);
        set_time_limit(0);

        // Retorna logo um "OK" e fecha a conexão se o servidor/SAPI suportar, para que a request do cURL acabe rápido
        if (function_exists('fastcgi_finish_request')) {
            echo "Cron de Disparos WAHA iniciado em background.";
            fastcgi_finish_request();
        } else {
            // Fallback: Manda headers forçando fechamento e limpa buffers (nem sempre corta a conexao no apache/php-fpm dependendo da config)
            ob_start();
            echo "Cron de Disparos WAHA iniciado. Rodando em background...\n";
            $size = ob_get_length();
            header("Content-Length: $size");
            header('Connection: close');
            ob_end_flush();
            if(ob_get_level() > 0) ob_flush();
            flush();
        }

        try {
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

            foreach ($appointments as $appt) {
                if ($appt['has_whatsapp']) {
                    WahaApiService::sendAppointmentConfirmation($appt['id']);
                    
                    // Delay humanoide entre 4 e 9 segundos entre cada disparo
                    sleep(rand(4, 9));
                }
            }

            // Log de sucesso
            file_put_contents(__DIR__ . '/../../public/cron_waha.log', date('Y-m-d H:i:s') . " - Executado com sucesso. " . count($appointments) . " agendamentos processados.\n", FILE_APPEND);

        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../../public/cron_waha.log', date('Y-m-d H:i:s') . " - ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}
