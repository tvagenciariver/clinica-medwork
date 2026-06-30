<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Core/Database.php';

use Core\Database;

$db = Database::getInstance();
$patients = $db->query("SELECT * FROM patients WHERE id NOT IN (SELECT patient_id FROM users WHERE role='patient')")->fetchAll();

foreach ($patients as $p) {
    $login = $p['cpf'];
    $password = password_hash($p['cpf'], PASSWORD_DEFAULT);
    
    $stmtUser = $db->prepare("
        INSERT INTO users (name, email, password, role, patient_id) 
        VALUES (:name, :email, :password, 'patient', :patient_id)
    ");
    try {
        $stmtUser->execute([
            'name' => $p['full_name'],
            'email' => $login,
            'password' => $password,
            'patient_id' => $p['id']
        ]);
        echo "Usuário criado para: {$p['full_name']} (CPF: {$login})\n";
    } catch (Exception $e) {
        echo "Erro para {$p['full_name']}: " . $e->getMessage() . "\n";
    }
}

// Also fix existing users where email might not be CPF but they want CPF
$users = $db->query("SELECT u.*, p.cpf FROM users u JOIN patients p ON u.patient_id = p.id WHERE u.role = 'patient'")->fetchAll();
foreach ($users as $u) {
    if ($u['email'] !== $u['cpf']) {
        try {
            $db->prepare("UPDATE users SET email = :cpf WHERE id = :id")->execute(['cpf' => $u['cpf'], 'id' => $u['id']]);
            echo "Login atualizado para CPF para: {$u['name']}\n";
        } catch (Exception $e) {
            echo "Erro ao atualizar login para {$u['name']}: " . $e->getMessage() . "\n";
        }
    }
}

echo "Pronto!";
