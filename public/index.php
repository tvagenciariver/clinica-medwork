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

// TEMPORARY DB MIGRATION: expand file_path column and create appointments table
try {
    $db = \Core\Database::getInstance();
    $db->query("ALTER TABLE exams MODIFY file_path TEXT");
    
    // Create appointments table
    $db->query("CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        procedure_name VARCHAR(150) NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        status ENUM('agendado', 'confirmado', 'cancelado', 'atendido', 'faltou') DEFAULT 'agendado',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
    )");
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
$router->add('GET', '/admin/appointments/getTomorrowIds', 'AppointmentController@getTomorrowIds');
$router->add('POST', '/admin/appointments/sendSingle', 'AppointmentController@sendSingle');
$router->add('GET', '/admin/appointments/cancel/{id}', 'AppointmentController@cancel');

// Webhook WAHA
$router->add('POST', '/webhook/waha', 'WebhookController@wahaReceiver');

// Automação / Cron
$router->add('GET', '/cron/waha-daily', 'CronController@runDailyWaha');

// Dispatch!
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
