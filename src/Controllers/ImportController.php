<?php
namespace Controllers;

use Core\Controller;
use Core\Database;

// Carregar SimpleXLSX (e XLS por via das dúvidas)
require_once __DIR__ . '/../Libs/SimpleXLSX.php';
require_once __DIR__ . '/../Libs/SimpleXLS.php';

class ImportController extends Controller {

    public function index() {
        $this->authRequired(['admin', 'employee']);
        
        $msg = $_SESSION['msg'] ?? null;
        $msg_type = $_SESSION['msg_type'] ?? null;
        $import_stats = $_SESSION['import_stats'] ?? null;
        
        unset($_SESSION['msg'], $_SESSION['msg_type'], $_SESSION['import_stats']);
        
        $this->view('admin/import/index', [
            'msg' => $msg,
            'msg_type' => $msg_type,
            'import_stats' => $import_stats
        ]);
    }

    public function process() {
        $this->authRequired(['admin', 'employee']);

        if ($this->isPost() && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['msg'] = 'Erro no upload do arquivo.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/import');
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['xlsx', 'xls'])) {
                $_SESSION['msg'] = 'Formato inválido. Por favor, envie um arquivo .xlsx ou .xls.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/import');
                return;
            }

            // Tentar ler a planilha
            if ($ext === 'xlsx') {
                $xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name']);
            } else {
                $xlsx = \Shuchkin\SimpleXLS::parse($file['tmp_name']);
            }

            if (!$xlsx) {
                $_SESSION['msg'] = 'Não foi possível ler o arquivo Excel: ' . (\Shuchkin\SimpleXLSX::parseError() ?? 'Erro desconhecido');
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/import');
                return;
            }

            $rows = $xlsx->rows();
            if (count($rows) <= 1) {
                $_SESSION['msg'] = 'O arquivo parece estar vazio ou só tem o cabeçalho.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('/admin/import');
                return;
            }

            $db = Database::getInstance();
            
            $stats = [
                'total' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'errors' => 0
            ];

            // Pular o cabeçalho (índice 0)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $stats['total']++;

                try {
                    $categoria = trim($row[1] ?? '');
                    $nome_exame = trim($row[2] ?? '');
                    $tipo_exame = trim($row[4] ?? '');
                    $empresa_nome = trim($row[6] ?? '');
                    $paciente_nome = trim($row[13] ?? '');
                    $cpf_bruto = trim($row[14] ?? '');
                    $data_exame_raw = trim($row[15] ?? '');

                    // Se não tiver CPF ou Paciente, ignorar a linha
                    if (empty($paciente_nome) || empty($cpf_bruto)) {
                        $stats['errors']++;
                        continue;
                    }

                    // Limpar CPF
                    $cpf = preg_replace('/[^0-9]/', '', $cpf_bruto);
                    
                    if (empty($cpf)) {
                        $stats['errors']++;
                        continue;
                    }

                    // Tratar Data (Pode vir como dd/mm/yyyy ou formato Excel serial)
                    if (is_numeric($data_exame_raw)) {
                        // Converter Excel Serial Date
                        $unix_date = ($data_exame_raw - 25569) * 86400;
                        $data_exame = gmdate("Y-m-d", $unix_date);
                    } else {
                        // Tentar fazer parse
                        $data_parts = explode('/', $data_exame_raw);
                        if (count($data_parts) === 3) {
                            $data_exame = $data_parts[2] . '-' . $data_parts[1] . '-' . $data_parts[0];
                        } else {
                            $data_exame = date('Y-m-d'); // Fallback se não conseguir ler
                        }
                    }

                    // Formatar o "exam_type" que o banco de dados do sistema espera
                    // Vamos unir [Categoria] Nome do Exame - Tipo
                    $full_exam_name = "";
                    if (!empty($categoria)) $full_exam_name .= "[$categoria] ";
                    $full_exam_name .= $nome_exame;
                    if (!empty($tipo_exame)) $full_exam_name .= " - $tipo_exame";
                    
                    if (empty(trim($full_exam_name))) {
                        $full_exam_name = "Exame Genérico";
                    }

                    // 1. GERENCIAR EMPRESA
                    $company_id = null;
                    if (!empty($empresa_nome)) {
                        $stmt = $db->prepare("SELECT id FROM companies WHERE corporate_name = ? OR trade_name = ?");
                        $stmt->execute([$empresa_nome, $empresa_nome]);
                        $company = $stmt->fetch();
                        if ($company) {
                            $company_id = $company['id'];
                        } else {
                            // Criar empresa genérica temporária baseada no nome
                            $stmt = $db->prepare("INSERT INTO companies (corporate_name, trade_name, cnpj) VALUES (?, ?, ?)");
                            $fake_cnpj = '000000' . rand(1000, 9999) . time();
                            $stmt->execute([$empresa_nome, $empresa_nome, $fake_cnpj]);
                            $company_id = $db->lastInsertId();
                        }
                    }

                    // 2. GERENCIAR PACIENTE E USUÁRIO
                    $stmt = $db->prepare("SELECT id FROM patients WHERE cpf = ?");
                    $stmt->execute([$cpf]);
                    $patient = $stmt->fetch();
                    
                    if ($patient) {
                        $patient_id = $patient['id'];
                    } else {
                        // Criar paciente
                        $stmt = $db->prepare("INSERT INTO patients (full_name, cpf, default_company_id) VALUES (?, ?, ?)");
                        $stmt->execute([$paciente_nome, $cpf, $company_id]);
                        $patient_id = $db->lastInsertId();

                        // Criar o usuário para o paciente (Login: CPF, Senha: CPF)
                        $hash = password_hash($cpf, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, patient_id, company_id, force_password_change) VALUES (?, ?, ?, 'patient', ?, ?, 1)");
                        $stmt->execute([$paciente_nome, $cpf, $hash, $patient_id, $company_id]);
                    }

                    // 3. GERENCIAR EXAME (ANTI-DUPLICAÇÃO)
                    // Checar se já tem esse exame nesse dia pra esse paciente
                    $stmt = $db->prepare("SELECT id FROM exams WHERE patient_id = ? AND exam_date = ? AND exam_type = ?");
                    $stmt->execute([$patient_id, $data_exame, $full_exam_name]);
                    if ($stmt->fetch()) {
                        // Duplicado
                        $stats['skipped']++;
                        continue;
                    }

                    // Inserir novo exame
                    $protocol = date('Y') . date('m') . strtoupper(substr(md5(uniqid()), 0, 5));
                    $origin = $company_id ? 'company' : 'private';

                    $stmt = $db->prepare("
                        INSERT INTO exams (patient_id, origin, company_id, exam_type, exam_date, protocol_code, created_by, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'registered')
                    ");
                    $stmt->execute([
                        $patient_id,
                        $origin,
                        $company_id,
                        $full_exam_name,
                        $data_exame,
                        $protocol,
                        $_SESSION['user_id']
                    ]);
                    
                    $stats['inserted']++;

                } catch (\Exception $e) {
                    $stats['errors']++;
                }
            }

            $_SESSION['import_stats'] = $stats;
            $_SESSION['msg'] = 'Importação concluída!';
            $_SESSION['msg_type'] = 'success';
            
            $this->redirect('/admin/import');
        }
    }
}
