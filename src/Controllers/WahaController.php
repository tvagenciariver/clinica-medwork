<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class WahaController extends Controller {
    
    public function index() {
        $this->authRequired(['admin']);
        
        $db = Database::getInstance();
        $settingsRaw = $db->query("SELECT * FROM settings")->fetchAll();
        $settings = [];
        foreach($settingsRaw as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $msg = $_SESSION['msg'] ?? null;
        $msg_type = $_SESSION['msg_type'] ?? null;
        unset($_SESSION['msg'], $_SESSION['msg_type']);

        $this->view('admin/waha/index', [
            'settings' => $settings,
            'msg' => $msg,
            'msg_type' => $msg_type
        ]);
    }

    public function store() {
        $this->authRequired(['admin']);
        
        if ($this->isPost()) {
            $db = Database::getInstance();
            
            $fields = [
                'waha_base_url',
                'waha_api_key',
                'waha_session',
                'waha_template_patient',
                'waha_template_company'
            ];

            try {
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                foreach ($fields as $field) {
                    $val = trim($_POST[$field] ?? '');
                    $stmt->execute([
                        'key' => $field,
                        'val' => $val
                    ]);
                }
                
                $_SESSION['msg'] = 'Configurações da WAHA atualizadas com sucesso!';
                $_SESSION['msg_type'] = 'success';
            } catch (\PDOException $e) {
                $_SESSION['msg'] = 'Erro ao salvar as configurações: ' . $e->getMessage();
                $_SESSION['msg_type'] = 'error';
            }
            
            $this->redirect('/admin/waha');
        }
    }
}
