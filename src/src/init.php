<?php
/**
 * Inicialização do Sistema - Versão Compatível com Hospedagem Compartilhada
 * Sistema Bichos do Bairro
 * 
 * Este arquivo inicializa o sistema SEM dependências externas
 */

// Definir constantes do sistema (apenas se não estiverem definidas)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/..');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Bichos do Bairro');
}

// Carregar configurações
require_once __DIR__ . '/Config.php';
Config::load();

// Configurar exibição de erros baseado no ambiente (DEVE ser feito antes de session_start)
if (Config::isDebug()) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Configurar sessão ANTES de iniciá-la
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de segurança da sessão
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.cookie_samesite', 'Lax');
    
    // Configurar tempo de vida da sessão
    ini_set('session.gc_maxlifetime', 3600); // 1 hora
    ini_set('session.cookie_lifetime', 3600); // 1 hora
}

// Carregar conexão com banco
require_once __DIR__ . '/db.php';

// Carregar classes principais
require_once __DIR__ . '/Utils.php';
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Cliente.php';
require_once __DIR__ . '/Pet.php';
require_once __DIR__ . '/Agendamento.php';
require_once __DIR__ . '/AgendamentoRecorrente.php';
require_once __DIR__ . '/Notificacao.php';
require_once __DIR__ . '/Cache.php';
require_once __DIR__ . '/Logger.php';

// Inicializar sessão se não estiver ativa (apenas se ainda não foi iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar headers de segurança básicos
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
}

// Função helper para obter URL base
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME']);
    
    return "$protocol://$host$path";
}

// Função helper para redirecionar
function redirect($url) {
    header("Location: $url");
    exit;
}

// Função helper para verificar se é requisição AJAX
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Função helper para resposta JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Função helper para sanitizar entrada
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Função helper para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função helper para gerar token CSRF
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função helper para verificar token CSRF
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Função helper para formatar data
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

// Função helper para formatar moeda
function formatCurrency($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

// Função helper para formatar telefone
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) === 11) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
    } elseif (strlen($phone) === 10) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
    }
    
    return $phone;
}

// Função helper para formatar CPF
function formatCpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    
    return $cpf;
}

// Função helper para obter IP do cliente
function getClientIp() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Função helper para log de erro simplificado
function logError($message, $context = []) {
    $logFile = APP_ROOT . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logMessage = "[$timestamp] ERROR: $message$contextStr" . PHP_EOL;
    
    // Criar diretório de logs se não existir
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Função helper para log de info simplificado
function logInfo($message, $context = []) {
    if (!Config::isDebug()) {
        return; // Só logar em desenvolvimento
    }
    
    $logFile = APP_ROOT . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logMessage = "[$timestamp] INFO: $message$contextStr" . PHP_EOL;
    
    // Criar diretório de logs se não existir
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Configurar handler de erro personalizado
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    logError("PHP Error: $message in $file on line $line");
    
    if (Config::isDebug()) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

// Configurar handler de exceção personalizado
set_exception_handler(function($exception) {
    logError("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (Config::isDebug()) {
        echo "<h1>Erro Fatal</h1>";
        echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Linha:</strong> " . $exception->getLine() . "</p>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>Erro Interno do Servidor</h1>";
        echo "<p>Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.</p>";
    }
    
    exit(1);
});

// Log de inicialização
logInfo('Sistema inicializado com sucesso', [
    'version' => APP_VERSION,
    'environment' => Config::get('APP_ENV'),
    'debug' => Config::isDebug()
]); 