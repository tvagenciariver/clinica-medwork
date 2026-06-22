<?php
// public/index.php

// Inicia sessão
session_start();

// Permite que o sistema seja embutido em Iframes de outros sites
header("Content-Security-Policy: frame-ancestors *");
header("X-Frame-Options: ALLOWALL");

// Configurações e requires base (substituindo composer autoload por algo simples)
require_once __DIR__ . '/../config/database.php';

// Autoloader simples
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ==========================================
// DB Connection
// ==========================================
try {
    $db = \Core\Database::getInstance();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ==========================================
// Global Settings Loader
// ==========================================
$appSettings = [];
try {
    $settingsRaw = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
    foreach($settingsRaw as $s) {
        $appSettings[$s['setting_key']] = $s['setting_value'];
    }
} catch (Exception $e) {
    // Ignore se a tabela ainda não existir
}

// Fallbacks caso as chaves não existam no banco
if (!isset($appSettings['company_name'])) $appSettings['company_name'] = 'MedWork';
if (!isset($appSettings['company_logo'])) $appSettings['company_logo'] = '';

// Variável global para ser usada nas views
$GLOBALS['appSettings'] = $appSettings;

// ==========================================
// Routing Configuration
// ==========================================
// TEMPORARY DB MIGRATION: expand file_path column and create appointments table
try {
    $db->query("ALTER TABLE exams MODIFY file_path TEXT");
    
    // Create specialties table
    $db->query("CREATE TABLE IF NOT EXISTS specialties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        color_hex VARCHAR(7) DEFAULT '#3b82f6',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert default specialties if empty
    $count = $db->query("SELECT COUNT(*) FROM specialties")->fetchColumn();
    if ($count == 0) {
        $db->query("INSERT INTO specialties (name, color_hex) VALUES ('Clínica Geral', '#3b82f6'), ('Raio-X', '#f59e0b'), ('Audiometria', '#10b981'), ('Psicologia', '#8b5cf6')");
    }

    // Create appointments table
    $db->query("CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        specialty_id INT DEFAULT NULL,
        procedure_name VARCHAR(150) NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        status ENUM('agendado', 'confirmado', 'cancelado', 'atendido', 'faltou') DEFAULT 'agendado',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
        FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE SET NULL
    )");

    // Migration to add specialty_id to existing appointments table
    $db->query("ALTER TABLE appointments ADD COLUMN specialty_id INT DEFAULT NULL AFTER patient_id");
    $db->query("ALTER TABLE appointments MODIFY procedure_name VARCHAR(150) NULL");
    $db->query("ALTER TABLE appointments ADD CONSTRAINT fk_specialty FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE SET NULL");
} catch (\Throwable $e) {
    // ignore if already done or errors
}

// Instancia Roteador
$router = new \Core\Router();

// ---- ROTAS DA APLICAÇÃO ----
// Login
$router->add('GET', '/', 'AuthController@index');
$router->add('GET', '/login', 'AuthController@index');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/logout', 'AuthController@logout');

// Dashboard Admin/Func
$router->add('GET', '/admin/dashboard', 'DashboardController@index');

// Pacientes
$router->add('GET', '/admin/patients', 'PatientController@index');
$router->add('GET', '/admin/patients/create', 'PatientController@create');
$router->add('POST', '/admin/patients/store', 'PatientController@store');
$router->add('GET', '/admin/patients/edit/{id}', 'PatientController@edit');
$router->add('POST', '/admin/patients/update/{id}', 'PatientController@update');

// Empresas
$router->add('GET', '/admin/companies', 'CompanyController@index');
$router->add('GET', '/admin/companies/create', 'CompanyController@create');
$router->add('POST', '/admin/companies/store', 'CompanyController@store');
$router->add('GET', '/admin/companies/edit/{id}', 'CompanyController@edit');
$router->add('POST', '/admin/companies/update/{id}', 'CompanyController@update');

// Exames
$router->add('GET', '/admin/exams', 'ExamController@index');
$router->add('GET', '/admin/exams/create', 'ExamController@create');
$router->add('POST', '/admin/exams/store', 'ExamController@store');
$router->add('GET', '/admin/exams/edit/{id}', 'ExamController@edit');
$router->add('POST', '/admin/exams/update/{id}', 'ExamController@update');
$router->add('GET', '/admin/exams/makeAvailable', 'ExamController@makeAvailable');
$router->add('GET', '/admin/exams/sendWaha', 'ExamController@sendWaha');

// Portais
$router->add('GET', '/patient/dashboard', 'PortalController@patient');
$router->add('GET', '/company/dashboard', 'PortalController@company');
$router->add('GET', '/portal/exam/view/{id}', 'PortalController@viewExam');

// Logs (Apenas Admin)
$router->add('GET', '/admin/logs', 'LogController@index');

// Painel WAHA
$router->add('GET', '/admin/waha', 'WahaController@index');
$router->add('POST', '/admin/waha/store', 'WahaController@store');

// Agendamentos
$router->add('GET', '/admin/appointments', 'AppointmentController@index');
$router->add('GET', '/admin/appointments/create', 'AppointmentController@create');
$router->add('POST', '/admin/appointments/store', 'AppointmentController@store');
$router->add('GET', '/admin/appointments/edit/{id}', 'AppointmentController@edit');
$router->add('POST', '/admin/appointments/update/{id}', 'AppointmentController@update');
$router->add('POST', '/admin/appointments/updateStatus/{id}', 'AppointmentController@updateStatus');
$router->add('POST', '/admin/appointments/updateStatusAjax/{id}', 'AppointmentController@updateStatusAjax');
$router->add('GET', '/admin/appointments/getTomorrowIds', 'AppointmentController@getTomorrowIds');
$router->add('POST', '/admin/appointments/sendSingle', 'AppointmentController@sendSingle');
$router->add('GET', '/admin/appointments/cancel/{id}', 'AppointmentController@cancel');

// Especialidades
$router->add('GET', '/admin/specialties', 'SpecialtyController@index');
$router->add('GET', '/admin/specialties/create', 'SpecialtyController@create');
$router->add('POST', '/admin/specialties/store', 'SpecialtyController@store');
$router->add('GET', '/admin/specialties/edit/{id}', 'SpecialtyController@edit');
$router->add('POST', '/admin/specialties/update/{id}', 'SpecialtyController@update');
$router->add('GET', '/admin/specialties/delete/{id}', 'SpecialtyController@delete');

// Webhook WAHA
$router->add('POST', '/webhook/waha', 'WebhookController@wahaReceiver');

// Automação / Cron
$router->add('GET', '/cron/waha-daily', 'CronController@runDailyWaha');

// Settings routes
$router->add('GET', '/admin/settings', 'SettingController@index');
$router->add('POST', '/admin/settings/update', 'SettingController@update');

// User Management routes
$router->add('GET', '/admin/users', 'UserController@index');
$router->add('GET', '/admin/users/create', 'UserController@create');
$router->add('POST', '/admin/users/store', 'UserController@store');
$router->add('GET', '/admin/users/edit/{id}', 'UserController@edit');
$router->add('POST', '/admin/users/update/{id}', 'UserController@update');
$router->add('GET', '/admin/users/delete/{id}', 'UserController@delete');

// ==========================================
// Dispatch
// ==========================================
// Pega o caminho relativo à pasta onde o script está rodando
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query strings da URI
if (($pos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}

// Remove o basePath se o sistema não estiver na raiz de um subdomínio
if (strpos($requestUri, $basePath) === 0 && $basePath !== '/') {
    $requestUri = substr($requestUri, strlen($basePath));
}

$requestUri = '/' . ltrim($requestUri, '/');

$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $method);
