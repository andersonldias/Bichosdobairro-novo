<?php
/**
 * Script de Deploy Web - Bichos do Bairro
 * Execute este script diretamente no servidor web para fazer o deploy
 */

echo "<h1>🚀 Deploy Web - Bichos do Bairro</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Verificar se estamos no servidor correto
$servidorAtual = $_SERVER['HTTP_HOST'] ?? 'localhost';
echo "<p><strong>Servidor atual:</strong> $servidorAtual</p>";

if ($servidorAtual !== 'meuapp.bichosdobairro.com.br') {
    echo "<div style='background: #fee2e2; padding: 20px; border-radius: 8px; border: 2px solid #ef4444;'>";
    echo "<h3 style='color: #dc2626; text-align: center;'>❌ ERRO</h3>";
    echo "<p style='color: #dc2626; text-align: center;'>Este script deve ser executado no servidor de produção!</p>";
    echo "</div>";
    exit;
}

echo "<h2>1. Verificando Estrutura de Arquivos</h2>";

$diretorios = ['public', 'src', 'sql', 'scripts', 'logs'];
$arquivosPrincipais = [
    'public/corrigir-sistema-completo.php',
    'public/login-simples.php',
    'public/dashboard.php',
    'src/Auth.php',
    'src/Database.php'
];

echo "<h3>Verificando Diretórios:</h3>";
foreach ($diretorios as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color: green;'>✅ Diretório existe: $dir</p>";
    } else {
        echo "<p style='color: red;'>❌ Diretório não existe: $dir</p>";
    }
}

echo "<h3>Verificando Arquivos Principais:</h3>";
foreach ($arquivosPrincipais as $arquivo) {
    if (file_exists($arquivo)) {
        $tamanho = filesize($arquivo);
        echo "<p style='color: green;'>✅ Arquivo existe: $arquivo ($tamanho bytes)</p>";
    } else {
        echo "<p style='color: red;'>❌ Arquivo não existe: $arquivo</p>";
    }
}

echo "<h2>2. Verificando Banco de Dados</h2>";

try {
    // Configurações do banco de dados
    $config = [
        'host' => 'mysql.bichosdobairro.com.br',
        'dbname' => 'bichosdobairro',
        'username' => 'bichosdobairro',
        'password' => '7oH57vlG#',
        'charset' => 'utf8mb4'
    ];
    
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p style='color: green;'>✅ Conexão com banco de dados estabelecida</p>";
    
    // Verificar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Tabelas encontradas:</strong> " . count($tabelas) . "</p>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        echo "<li>✅ $tabela</li>";
    }
    echo "</ul>";
    
    // Verificar usuário administrador
    $stmt = $pdo->prepare("SELECT id, nome, email, nivel_acesso, ativo FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@bichosdobairro.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color: green;'>✅ Usuário administrador encontrado</p>";
        echo "<p><strong>ID:</strong> " . $admin['id'] . "</p>";
        echo "<p><strong>Nome:</strong> " . htmlspecialchars($admin['nome']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p><strong>Nível:</strong> " . $admin['nivel_acesso'] . "</p>";
        echo "<p><strong>Ativo:</strong> " . ($admin['ativo'] ? 'Sim' : 'Não') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Usuário administrador não encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no banco de dados: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>3. Status do Sistema</h2>";

// Verificar se o script de correção existe
if (file_exists('corrigir-sistema-completo.php')) {
    echo "<p style='color: green;'>✅ Script de correção disponível</p>";
} else {
    echo "<p style='color: red;'>❌ Script de correção não encontrado</p>";
}

// Verificar permissões de escrita
$diretoriosTeste = ['logs', 'public'];
foreach ($diretoriosTeste as $dir) {
    if (is_writable($dir)) {
        echo "<p style='color: green;'>✅ Diretório $dir tem permissão de escrita</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Diretório $dir sem permissão de escrita</p>";
    }
}

echo "<h2>4. Ações Recomendadas</h2>";

echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
echo "<h3 style='color: #059669;'>🎯 Próximos Passos</h3>";
echo "<ol>";
echo "<li><strong>Execute o script de correção:</strong> <a href='corrigir-sistema-completo.php' target='_blank'>corrigir-sistema-completo.php</a></li>";
echo "<li><strong>Teste o login:</strong> <a href='login-simples.php' target='_blank'>login-simples.php</a></li>";
echo "<li><strong>Verifique o dashboard:</strong> <a href='dashboard.php' target='_blank'>dashboard.php</a></li>";
echo "<li><strong>Teste as permissões:</strong> <a href='admin-permissoes.php' target='_blank'>admin-permissoes.php</a></li>";
echo "<li><strong>Teste alterar senha:</strong> <a href='alterar-senha.php' target='_blank'>alterar-senha.php</a></li>";
echo "</ol>";
echo "</div>";

echo "<h3>📋 Credenciais de Acesso</h3>";
echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; border: 2px solid #f59e0b;'>";
echo "<p><strong>Email:</strong> admin@bichosdobairro.com</p>";
echo "<p><strong>Senha:</strong> admin123</p>";
echo "</div>";

echo "<h3>🔗 Links de Teste</h3>";
echo "<ul>";
echo "<li><a href='corrigir-sistema-completo.php' target='_blank'>🔧 Corrigir Sistema Completo</a></li>";
echo "<li><a href='login-simples.php' target='_blank'>🔐 Login</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>📊 Dashboard</a></li>";
echo "<li><a href='admin-permissoes.php' target='_blank'>⚙️ Admin Permissões</a></li>";
echo "<li><a href='admin-usuarios.php' target='_blank'>👥 Admin Usuários</a></li>";
echo "<li><a href='admin-niveis.php' target='_blank'>📋 Admin Níveis</a></li>";
echo "<li><a href='alterar-senha.php' target='_blank'>🔑 Alterar Senha</a></li>";
echo "<li><a href='agendamentos.php' target='_blank'>📅 Agendamentos</a></li>";
echo "<li><a href='agendamentos-recorrentes.php' target='_blank'>🔄 Agendamentos Recorrentes</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Deploy verificado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> $servidorAtual</p>";
?> 