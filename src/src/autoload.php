<?php
/**
 * Sistema de autoload personalizado
 */

// Registrar função de autoload
spl_autoload_register(function ($class) {
    // Mapeamento de classes para arquivos
    $classMap = [
        'Config' => __DIR__ . '/Config.php',
        'Utils' => __DIR__ . '/Utils.php',
        'BaseModel' => __DIR__ . '/BaseModel.php',
        'Cliente' => __DIR__ . '/Cliente.php',
        'Pet' => __DIR__ . '/Pet.php',
        'Agendamento' => __DIR__ . '/Agendamento.php',
    ];
    
    // Verificar se a classe está no mapeamento
    if (isset($classMap[$class])) {
        require_once $classMap[$class];
        return true;
    }
    
    // Tentar carregar automaticamente baseado no nome da classe
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// Inicializar configurações globais
Config::init();

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir constantes úteis
define('APP_ROOT', dirname(__DIR__));
define('SRC_PATH', __DIR__);
define('PUBLIC_PATH', APP_ROOT . '/public');
define('LOGS_PATH', APP_ROOT . '/logs');

// Criar diretório de logs se não existir
if (!is_dir(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

// Configurar handler de erros personalizado
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    $errorType = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED'
    ];
    
    $type = $errorType[$severity] ?? 'UNKNOWN';
    
    Utils::logError("[$type] $message", [
        'file' => $file,
        'line' => $line,
        'severity' => $severity
    ]);
    
    if (Config::getAppConfig('debug')) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

// Configurar handler de exceções
set_exception_handler(function ($exception) {
    Utils::logError('Uncaught Exception: ' . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (Config::getAppConfig('debug')) {
        echo '<h1>Erro Fatal</h1>';
        echo '<p><strong>Mensagem:</strong> ' . $exception->getMessage() . '</p>';
        echo '<p><strong>Arquivo:</strong> ' . $exception->getFile() . '</p>';
        echo '<p><strong>Linha:</strong> ' . $exception->getLine() . '</p>';
        echo '<h2>Stack Trace:</h2>';
        echo '<pre>' . $exception->getTraceAsString() . '</pre>';
    } else {
        echo '<h1>Erro Interno</h1>';
        echo '<p>Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.</p>';
    }
    
    exit(1);
});

// Função para carregar todas as dependências necessárias
function loadDependencies() {
    // Carregar classes principais
    require_once __DIR__ . '/db.php';
    
    // Verificar se todas as classes essenciais estão disponíveis
    $requiredClasses = ['Config', 'Utils', 'BaseModel', 'Cliente', 'Pet', 'Agendamento'];
    foreach ($requiredClasses as $class) {
        if (!class_exists($class)) {
            // Log do erro mas não falhar
            error_log("Classe não encontrada: $class");
        }
    }
}

// Carregar dependências
loadDependencies(); 