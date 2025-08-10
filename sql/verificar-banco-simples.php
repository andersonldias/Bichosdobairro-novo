<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Script de Verificação Simples do Banco de Dados
 * Sistema Bichos do Bairro
 */

echo "<h1>🔍 Verificação Simples do Banco de Dados</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Configurações do banco de dados
$config = [
    'host' => 'mysql.bichosdobairro.com.br',
    'dbname' => 'bichosdobairro',
    'username' => 'bichosdobairro',
    'password' => '7oH57vlG#',
    'charset' => 'utf8mb4'
];

try {
    echo "<h2>1. Testando Conexão</h2>";
    
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    echo "<h2>2. Verificando Tabelas Existentes</h2>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Total de tabelas encontradas:</strong> " . count($tabelas) . "</p>";
    
    if (count($tabelas) > 0) {
        echo "<h3>Tabelas encontradas:</h3>";
        echo "<ul>";
        foreach ($tabelas as $tabela) {
            echo "<li>✅ $tabela</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Nenhuma tabela encontrada!</p>";
    }
    
    echo "<h2>3. Verificando Tabelas Essenciais</h2>";
    
    $tabelasEssenciais = [
        'usuarios',
        'permissoes',
        'usuarios_permissoes',
        'niveis_acesso',
        'logs_login',
        'logs_atividade',
        'clientes',
        'pets',
        'agendamentos',
        'agendamentos_recorrentes',
        'logs_agendamentos_recorrentes',
        'notificacoes',
        'telefones',
        'configuracoes'
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f3f4f6;'><th>Tabela</th><th>Status</th></tr>";
    
    $tabelasExistentes = 0;
    foreach ($tabelasEssenciais as $tabela) {
        $existe = in_array($tabela, $tabelas);
        $status = $existe ? "✅ EXISTE" : "❌ NÃO EXISTE";
        $cor = $existe ? "green" : "red";
        
        if ($existe) {
            $tabelasExistentes++;
        }
        
        echo "<tr>";
        echo "<td><strong>$tabela</strong></td>";
        echo "<td style='color: $cor;'>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>4. Resumo</h2>";
    
    $percentual = round(($tabelasExistentes / count($tabelasEssenciais)) * 100, 1);
    
    echo "<p><strong>Tabelas essenciais encontradas:</strong> $tabelasExistentes de " . count($tabelasEssenciais) . " ($percentual%)</p>";
    
    if ($tabelasExistentes == 0) {
        echo "<div style='background: #fee2e2; padding: 20px; border-radius: 8px; border: 2px solid #ef4444;'>";
        echo "<h3 style='color: #dc2626; text-align: center;'>❌ BANCO VAZIO!</h3>";
        echo "<p style='color: #dc2626; text-align: center;'>Nenhuma tabela essencial foi encontrada.</p>";
        echo "<p style='color: #dc2626; text-align: center;'>É necessário executar o script de sincronização.</p>";
        echo "</div>";
    } elseif ($tabelasExistentes < count($tabelasEssenciais)) {
        echo "<div style='background: #fef3c7; padding: 20px; border-radius: 8px; border: 2px solid #f59e0b;'>";
        echo "<h3 style='color: #d97706; text-align: center;'>⚠️ BANCO INCOMPLETO</h3>";
        echo "<p style='color: #d97706; text-align: center;'>Algumas tabelas estão faltando.</p>";
        echo "<p style='color: #d97706; text-align: center;'>Execute o script de sincronização para corrigir.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
        echo "<h3 style='color: #059669; text-align: center;'>✅ BANCO CONFIGURADO!</h3>";
        echo "<p style='color: #059669; text-align: center;'>Todas as tabelas essenciais foram encontradas.</p>";
        echo "</div>";
    }
    
    echo "<h2>5. Próximos Passos</h2>";
    
    if ($tabelasExistentes == 0) {
        echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; border: 2px solid #f59e0b;'>";
        echo "<h3 style='color: #d97706;'>🚨 AÇÃO NECESSÁRIA</h3>";
        echo "<p><strong>1. Execute o script de sincronização:</strong></p>";
        echo "<p><a href='sincronizar-banco.php' target='_blank' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 SINCRONIZAR BANCO</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d1fae5; padding: 15px; border-radius: 8px; border: 2px solid #10b981;'>";
        echo "<h3 style='color: #059669;'>✅ TESTE O SISTEMA</h3>";
        echo "<p><strong>Links para teste:</strong></p>";
        echo "<ul>";
        echo "<li><a href='login-simples.php' target='_blank'>🔐 Login</a></li>";
        echo "<li><a href='dashboard.php' target='_blank'>📊 Dashboard</a></li>";
        echo "<li><a href='admin-permissoes.php' target='_blank'>⚙️ Admin Permissões</a></li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h3>📋 Credenciais de Acesso</h3>";
    echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; border: 2px solid #f59e0b;'>";
    echo "<p><strong>Email:</strong> admin@bichosdobairro.com</p>";
    echo "<p><strong>Senha:</strong> admin123</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 20px; border-radius: 8px; border: 2px solid #ef4444;'>";
    echo "<h3 style='color: #dc2626; text-align: center;'>❌ ERRO NA VERIFICAÇÃO</h3>";
    echo "<p style='color: #dc2626;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    
    echo "<h3>🔧 Soluções Possíveis</h3>";
    echo "<ol>";
    echo "<li><strong>Verifique as credenciais do banco</strong></li>";
    echo "<li><strong>Verifique se o banco existe</strong></li>";
    echo "<li><strong>Execute o script de sincronização</strong></li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><strong>Verificação executada em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 