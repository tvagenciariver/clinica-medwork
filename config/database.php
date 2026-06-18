<?php
// config/database.php

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'clinica_db');

// WAHA API Config (Apenas Fallbacks, os reais vem do Banco de Dados)
define('WAHA_BASE_URL', 'http://localhost:3000'); 
define('WAHA_API_KEY', '');
define('WAHA_SESSION', 'default');

// URL Base da Aplicação (Pegará do Docker ou Localhost dependendo de como for servido)
// Se não encontrar BASE_URL no env, tenta descobrir dinamicamente.
$defaultBaseUrl = 'http://localhost/cdtlab/public';
if (getenv('BASE_URL')) {
    $defaultBaseUrl = getenv('BASE_URL');
} else if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $defaultBaseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . ($path === '/' || $path === '\\' ? '' : $path);
}

define('BASE_URL', $defaultBaseUrl);
