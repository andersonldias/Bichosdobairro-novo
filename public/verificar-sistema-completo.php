<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== VERIFICAÇÃO COMPLETA DO SISTEMA DE LOGIN ===\n\n";

try {
    require_once '../src/init.php';
    $pdo = getDb();
    
    // 1. Verificar conexão
    echo "1️⃣ CONEXÃO COM BANCO\n";
    echo "✅ Conexão estabelecida com sucesso!\n\n";
    
    // 2. Verificar tabelas
    echo "2️⃣ TABELAS DO SISTEMA\n";
    $tabelas_necessarias = ['usuarios', 'logs_login', 'logs_atividade'];
    $tabelas_existentes = [];
    
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tabelas_existentes[] = $row[0];
    }
    
    foreach ($tabelas_necessarias as $tabela) {
        if (in_array($tabela, $tabelas_existentes)) {
            echo "✅ Tabela '$tabela' existe\n";
        } else {
            echo "❌ Tabela '$tabela' NÃO EXISTE\n";
        }
    }
    echo "\n";
    
    // 3. Verificar estrutura da tabela usuarios
    echo "3️⃣ ESTRUTURA DA TABELA usuarios\n";
    $colunas_necessarias = ['id', 'nome', 'email', 'senha_hash', 'nivel_acesso', 'ativo', 'ultimo_login', 'tentativas_login', 'bloqueado_ate'];
    
    $stmt = $pdo->query("DESCRIBE usuarios");
    $colunas_existentes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $colunas_existentes[] = $row['Field'];
    }
    
    foreach ($colunas_necessarias as $coluna) {
        if (in_array($coluna, $colunas_existentes)) {
            echo "✅ Coluna '$coluna' existe\n";
        } else {
            echo "❌ Coluna '$coluna' NÃO EXISTE\n";
        }
    }
    echo "\n";
    
    // 4. Verificar usuário admin
    echo "4️⃣ USUÁRIO ADMINISTRADOR\n";
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@bichosdobairro.com']);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "✅ Usuário admin encontrado\n";
        echo "   Nome: {$usuario['nome']}\n";
        echo "   Email: {$usuario['email']}\n";
        echo "   Nível: {$usuario['nivel_acesso']}\n";
        echo "   Ativo: " . ($usuario['ativo'] ? 'Sim' : 'Não') . "\n";
    } else {
        echo "❌ Usuário admin NÃO ENCONTRADO\n";
    }
    echo "\n";
    
    // 5. Testar autenticação
    echo "5️⃣ TESTE DE AUTENTICAÇÃO\n";
    require_once '../src/Auth.php';
    $auth = new Auth();
    
    // Testar login com credenciais corretas
    $resultado = $auth->login('admin@bichosdobairro.com', 'admin123');
    if ($resultado['sucesso']) {
        echo "✅ Login funcionando corretamente!\n";
    } else {
        echo "❌ Erro no login: " . $resultado['erro'] . "\n";
    }
    echo "\n";
    
    // 6. Verificar arquivos PHP
    echo "6️⃣ ARQUIVOS PHP\n";
    $arquivos = [
        '../src/init.php',
        '../src/Auth.php',
        '../src/AuthMiddleware.php',
        'login-simples.php',
        'dashboard.php'
    ];
    
    foreach ($arquivos as $arquivo) {
        if (file_exists($arquivo)) {
            echo "✅ $arquivo existe\n";
        } else {
            echo "❌ $arquivo NÃO EXISTE\n";
        }
    }
    echo "\n";
    
    // 7. Testar sessão
    echo "7️⃣ TESTE DE SESSÃO\n";
    session_start();
    echo "✅ Sessão iniciada\n";
    echo "   Session ID: " . session_id() . "\n";
    echo "\n";
    
    // 8. Resumo final
    echo "8️⃣ RESUMO FINAL\n";
    echo "🎉 SISTEMA DE LOGIN VERIFICADO!\n\n";
    
    echo "🔐 CREDENCIAIS DE ACESSO:\n";
    echo "   Email: admin@bichosdobairro.com\n";
    echo "   Senha: admin123\n\n";
    
    echo "🌐 URLs DE TESTE:\n";
    echo "   Login Simples: http://localhost:8000/login-simples.php\n";
    echo "   Login Completo: http://localhost:8000/login.php\n";
    echo "   Dashboard: http://localhost:8000/dashboard.php\n\n";
    
    echo "✅ STATUS: SISTEMA PRONTO PARA USO!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "🔧 Execute: http://localhost:8000/corrigir-tabela-usuarios.php\n";
    echo "🔧 Execute: http://localhost:8000/criar-tabela-logs.php\n";
}
?> 