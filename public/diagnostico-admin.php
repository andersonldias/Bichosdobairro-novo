<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/init.php';
$pdo = getDb();
$email = 'admin@bichosdobairro.com';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "=== DIAGNÓSTICO USUÁRIO ADMIN ===\n\n";
    foreach ($usuario as $campo => $valor) {
        echo "$campo: $valor\n";
    }
    echo "\n---\n";
    echo "Hash salvo: {$usuario['senha_hash']}\n";
    echo "Hash esperado (admin123): $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\n";
    echo "Senha confere? ";
    if (password_verify('admin123', $usuario['senha_hash'])) {
        echo "SIM\n";
    } else {
        echo "NÃO\n";
    }
    echo "\nAtivo: " . ($usuario['ativo'] ? 'Sim' : 'Não') . "\n";
    echo "Tentativas de login: {$usuario['tentativas_login']}\n";
    echo "Bloqueado até: " . ($usuario['bloqueado_ate'] ? $usuario['bloqueado_ate'] : 'Não') . "\n";
} else {
    echo "Usuário admin não encontrado no banco!\n";
}
?> 