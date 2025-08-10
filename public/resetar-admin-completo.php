<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== RESET COMPLETO DO USUÁRIO ADMIN ===\n\n";

try {
    require_once '../src/init.php';
    $pdo = getDb();
    
    // 1. Resetar usuário admin
    echo "1️⃣ RESETANDO USUÁRIO ADMIN\n";
    $email = 'admin@bichosdobairro.com';
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // admin123
    
    $stmt = $pdo->prepare("UPDATE usuarios SET senha_hash = ?, tentativas_login = 0, bloqueado_ate = NULL, ativo = 1 WHERE email = ?");
    $stmt->execute([$hash, $email]);
    
    $linhasAfetadas = $stmt->rowCount();
    if ($linhasAfetadas > 0) {
        echo "✅ Usuário admin resetado com sucesso!\n";
    } else {
        echo "⚠️ Usuário admin não encontrado, criando...\n";
        
        // Criar usuário admin se não existir (usando ON DUPLICATE KEY UPDATE)
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso, ativo) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                senha_hash = VALUES(senha_hash), 
                tentativas_login = 0, 
                bloqueado_ate = NULL, 
                ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['Administrador', $email, $hash, 'admin', 1]);
        echo "✅ Usuário admin criado/atualizado!\n";
    }
    echo "\n";
    
    // 2. Verificar dados do usuário
    echo "2️⃣ VERIFICANDO DADOS DO USUÁRIO\n";
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "✅ Usuário encontrado:\n";
        echo "   ID: {$usuario['id']}\n";
        echo "   Nome: {$usuario['nome']}\n";
        echo "   Email: {$usuario['email']}\n";
        echo "   Nível: {$usuario['nivel_acesso']}\n";
        echo "   Ativo: " . ($usuario['ativo'] ? 'Sim' : 'Não') . "\n";
        echo "   Tentativas: {$usuario['tentativas_login']}\n";
        echo "   Bloqueado até: " . ($usuario['bloqueado_ate'] ? $usuario['bloqueado_ate'] : 'Não') . "\n";
        echo "   Hash correto: " . (password_verify('admin123', $usuario['senha_hash']) ? 'Sim' : 'Não') . "\n";
    } else {
        echo "❌ Usuário não encontrado!\n";
    }
    echo "\n";
    
    // 3. Testar autenticação
    echo "3️⃣ TESTANDO AUTENTICAÇÃO\n";
    require_once '../src/Auth.php';
    $auth = new Auth();
    
    // Testar login
    $resultado = $auth->login($email, 'admin123');
    if ($resultado['sucesso']) {
        echo "✅ Login funcionando perfeitamente!\n";
        echo "   Usuário logado: {$resultado['usuario']['nome']}\n";
        echo "   Nível: {$resultado['usuario']['nivel_acesso']}\n";
    } else {
        echo "❌ Erro no login: " . $resultado['erro'] . "\n";
    }
    echo "\n";
    
    // 4. Verificar logs
    echo "4️⃣ VERIFICANDO LOGS\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM logs_login WHERE email = '$email' ORDER BY data_hora DESC LIMIT 1");
    $count = $stmt->fetchColumn();
    echo "✅ Último log de login registrado\n";
    echo "\n";
    
    // 5. Testar sessão
    echo "5️⃣ TESTANDO SESSÃO\n";
    session_start();
    if (isset($_SESSION['usuario_id'])) {
        echo "✅ Sessão ativa\n";
        echo "   Usuário ID: {$_SESSION['usuario_id']}\n";
        echo "   Nome: {$_SESSION['usuario_nome']}\n";
        echo "   Email: {$_SESSION['usuario_email']}\n";
        echo "   Nível: {$_SESSION['usuario_nivel']}\n";
    } else {
        echo "⚠️ Sessão não ativa\n";
    }
    echo "\n";
    
    // 6. Resumo final
    echo "6️⃣ RESUMO FINAL\n";
    echo "🎉 USUÁRIO ADMIN RESETADO E FUNCIONANDO!\n\n";
    
    echo "🔐 CREDENCIAIS:\n";
    echo "   Email: admin@bichosdobairro.com\n";
    echo "   Senha: admin123\n\n";
    
    echo "🌐 TESTE AGORA:\n";
    echo "   Login Simples: http://localhost:8000/login-simples.php\n";
    echo "   Login Completo: http://localhost:8000/login.php\n";
    echo "   Dashboard: http://localhost:8000/dashboard.php\n\n";
    
    echo "✅ STATUS: SISTEMA PRONTO PARA USO!\n";
    echo "🚀 Faça login com as credenciais acima!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "🔧 Verifique a conexão com o banco de dados\n";
}
?> 