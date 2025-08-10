<?php
/**
 * Página Inicial - Sistema Bichos do Bairro
 * Redireciona para o dashboard ou login
 */

// Carregar configurações
require_once '../src/init.php';

// Verificar se está logado
if (isset($_SESSION['usuario_id'])) {
    // Se logado, redirecionar para dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Se não logado, redirecionar para login
    header('Location: login.php');
    exit;
}
?> 