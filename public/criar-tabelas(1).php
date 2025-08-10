<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Criar Tabelas do Sistema</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

try {
    require_once '../src/init.php';
    $pdo = getDb();
    echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na conexão com banco: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar se as tabelas já existem
$tabelasExistentes = [];
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tabelasExistentes[] = $row[0];
}

echo "<h2>1. Verificação de Tabelas Existentes</h2>";
echo "<p class='info'>📋 Tabelas encontradas no banco:</p>";
echo "<pre>";
foreach ($tabelasExistentes as $tabela) {
    echo "- $tabela\n";
}
echo "</pre>";

// Processar criação das tabelas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>2. Criando Tabelas</h2>";
    
    // Ler arquivo SQL
    $sqlFile = '../sql/create_usuarios_table.sql';
    if (!file_exists($sqlFile)) {
        echo "<p class='error'>❌ Arquivo SQL não encontrado: $sqlFile</p>";
        exit;
    }
    
    $sql = file_get_contents($sqlFile);
    $queries = explode(';', $sql);
    
    $sucessos = 0;
    $erros = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query) || strpos($query, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($query);
            echo "<p class='success'>✅ Query executada com sucesso</p>";
            $sucessos++;
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro na query: " . $e->getMessage() . "</p>";
            echo "<pre>" . htmlspecialchars($query) . "</pre>";
            $erros++;
        }
    }
    
    echo "<h3>Resumo:</h3>";
    echo "<p class='success'>✅ Queries executadas com sucesso: $sucessos</p>";
    if ($erros > 0) {
        echo "<p class='error'>❌ Queries com erro: $erros</p>";
    }
    
    // Verificar se as tabelas foram criadas
    echo "<h2>3. Verificação Final</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tabelasFinais = [];
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tabelasFinais[] = $row[0];
    }
    
    echo "<p class='info'>📋 Tabelas após criação:</p>";
    echo "<pre>";
    foreach ($tabelasFinais as $tabela) {
        echo "- $tabela\n";
    }
    echo "</pre>";
    
    // Verificar se há usuário admin
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE email = 'admin@bichosdobairro.com'");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($total > 0) {
        echo "<p class='success'>✅ Usuário administrador encontrado</p>";
        echo "<p class='info'>📋 Credenciais padrão:</p>";
        echo "<ul>";
        echo "<li><strong>E-mail:</strong> admin@bichosdobairro.com</li>";
        echo "<li><strong>Senha:</strong> admin123</li>";
        echo "</ul>";
        echo "<p class='warning'>⚠️ IMPORTANTE: Altere a senha após o primeiro login!</p>";
    } else {
        echo "<p class='warning'>⚠️ Usuário administrador não encontrado</p>";
    }
    
    echo "<p><a href='criar-admin.php' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Criar Usuário Administrador</a></p>";
    echo "<p><a href='login.php' style='background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Ir para Login</a></p>";
    
} else {
    // Formulário para criar tabelas
    ?>
    <form method="post" style="max-width:600px; margin:20px 0;">
        <h2>Criar Tabelas do Sistema</h2>
        
        <div style="background:#fff3cd; border:1px solid #ffeaa7; color:#856404; padding:15px; border-radius:5px; margin:20px 0;">
            <h3>⚠️ Informações Importantes:</h3>
            <ul>
                <li>Este script criará as tabelas necessárias para o sistema de login</li>
                <li>Será criado um usuário administrador padrão</li>
                <li>As tabelas incluem: usuarios, logs_login</li>
                <li>Certifique-se de que o banco de dados está configurado corretamente</li>
            </ul>
        </div>
        
        <div style="margin-bottom:20px;">
            <input type="submit" value="Criar Tabelas" 
                   style="background:#28a745; color:white; padding:12px 24px; border:none; border-radius:5px; cursor:pointer; font-size:16px;">
        </div>
    </form>
    
    <h3>Estrutura das Tabelas:</h3>
    <div style="background:#f8f9fa; padding:15px; border-radius:5px; margin:20px 0;">
        <h4>Tabela: usuarios</h4>
        <ul>
            <li>id (INT, AUTO_INCREMENT, PRIMARY KEY)</li>
            <li>nome (VARCHAR(100), NOT NULL)</li>
            <li>email (VARCHAR(100), NOT NULL, UNIQUE)</li>
            <li>senha_hash (VARCHAR(255), NOT NULL)</li>
            <li>nivel_acesso (ENUM('admin', 'usuario'), DEFAULT 'usuario')</li>
            <li>ativo (BOOLEAN, DEFAULT TRUE)</li>
            <li>ultimo_login (TIMESTAMP, NULL)</li>
            <li>tentativas_login (INT, DEFAULT 0)</li>
            <li>bloqueado_ate (TIMESTAMP, NULL)</li>
            <li>criado_em (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)</li>
            <li>atualizado_em (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)</li>
        </ul>
        
        <h4>Tabela: logs_login</h4>
        <ul>
            <li>id (INT, AUTO_INCREMENT, PRIMARY KEY)</li>
            <li>usuario_id (INT, NULL, FOREIGN KEY)</li>
            <li>email (VARCHAR(100), NOT NULL)</li>
            <li>ip_address (VARCHAR(45), NOT NULL)</li>
            <li>user_agent (TEXT)</li>
            <li>sucesso (BOOLEAN, NOT NULL)</li>
            <li>data_hora (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)</li>
        </ul>
    </div>
    
    <p><a href="diagnostico-login.php" style="background:#6c757d; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Diagnóstico do Sistema</a></p>
    <p><a href="login.php" style="background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Voltar ao Login</a></p>
    <?php
}
?> 