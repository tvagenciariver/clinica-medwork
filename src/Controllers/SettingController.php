<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

class SettingController extends Controller {

    public function index() {
        $this->authRequired(['admin']);
        
        $db = Database::getInstance();
        $settingsRaw = $db->query("SELECT * FROM settings")->fetchAll();
        $settings = [];
        foreach($settingsRaw as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }

        $msg = $_SESSION['msg'] ?? null;
        $msg_type = $_SESSION['msg_type'] ?? null;
        unset($_SESSION['msg'], $_SESSION['msg_type']);

        $this->view('admin/settings/index', [
            'settings' => $settings,
            'msg' => $msg,
            'msg_type' => $msg_type
        ]);
    }

    public function update() {
        $this->authRequired(['admin']);
        
        if ($this->isPost()) {
            $db = Database::getInstance();
            
            // Handle file upload for logo
            $logoPath = null;
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                
                $mimeType = $_FILES['company_logo']['type'];
                $imageInfo = @getimagesize($_FILES['company_logo']['tmp_name']);
                
                if ($imageInfo !== false) {
                    $mimeType = $imageInfo['mime'];
                }

                if (in_array($mimeType, $allowedMimeTypes)) {
                    $ext = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
                    $fileName = 'logo_' . time() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../../public/uploads/logo/';
                    
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $destination = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $destination)) {
                        $logoPath = '/uploads/logo/' . $fileName;
                    }
                }
            }

            // Text fields
            $keys = ['company_name', 'company_cnpj', 'company_phone', 'company_email', 'company_address'];
            
            try {
                $db->beginTransaction();
                
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                
                foreach($keys as $key) {
                    if (isset($_POST[$key])) {
                        $stmt->execute([$key, $_POST[$key]]);
                    }
                }

                // Salva o logo apenas se foi feito upload de um novo
                if ($logoPath) {
                    $stmt->execute(['company_logo', $logoPath]);
                }

                $db->commit();
                
                $_SESSION['msg'] = 'Configurações atualizadas com sucesso!';
                $_SESSION['msg_type'] = 'success';
            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['msg'] = 'Erro ao salvar configurações: ' . $e->getMessage();
                $_SESSION['msg_type'] = 'error';
            }

            $this->redirect('/admin/settings');
        }
    }
}
