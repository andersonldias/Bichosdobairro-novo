<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../src/init.php';
require_once '../src/Auth.php';

$auth = new Auth();

// Fazer logout
$auth->logout();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie da sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para login com mensagem
header('Location: login-simples.php?msg=logout');
exit; 