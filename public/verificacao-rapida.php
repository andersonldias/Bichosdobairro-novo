<?php
/**
 * Verificação Rápida do Sistema
 * Sistema Bichos do Bairro
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>⚡ Verificação Rápida do Sistema</h1>";

$problemas = [];
$sucessos = [];

// 1. Verificar sintaxe dos arquivos principais
echo "<h2>1. Verificação de Sintaxe</h2>";

$arquivosPrincipais = [
    '../src/init.php',
    '../src/db.php',
    '../src/Config.php',
    'clientes.php',
    'agendamentos.php',
    'login.php'
];

foreach ($arquivosPrincipais as $arquivo) {
    $output = [];
    $returnCode = 0;
    exec("php -l \"$arquivo\" 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        $sucessos[] = "✅ Sintaxe de " . basename($arquivo) . " OK";
        echo "<p style='color: green;'>✅ " . basename($arquivo) . " - OK</p>";
    } else {
        $problemas[] = "❌ Erro de sintaxe em " . basename($arquivo);
        echo "<p style='color: red;'>❌ " . basename($arquivo) . " - ERRO</p>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
    }
}

// 2. Verificar configurações
echo "<h2>2. Verificação de Configurações</h2>";

try {
    require_once '../src/Config.php';
    Config::load();
    $config = Config::all();
    
    // Verificar se as configurações de banco são válidas
    if ($config['DB_NAME'] === 'seu_banco_de_dados') {
        $problemas[] = "❌ Configuração de banco não definida";
        echo "<p style='color: red;'>❌ Configuração de banco não definida</p>";
        echo "<p>Use: <a href='configurar-banco.php'>configurar-banco.php</a></p>";
    } else {
        $sucessos[] = "✅ Configurações de banco definidas";
        echo "<p style='color: green;'>✅ Configurações de banco definidas</p>";
    }
    
} catch (Exception $e) {
    $problemas[] = "❌ Erro ao carregar configurações: " . $e->getMessage();
    echo "<p style='color: red;'>❌ Erro ao carregar configurações: " . $e->getMessage() . "</p>";
}

// 3. Verificar conexão com banco
echo "<h2>3. Verificação de Conexão com Banco</h2>";

try {
    require_once '../src/db.php';
    $pdo = getDb();
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result) {
        $sucessos[] = "✅ Conexão com banco OK";
        echo "<p style='color: green;'>✅ Conexão com banco OK</p>";
    } else {
        $problemas[] = "❌ Falha na query de teste";
        echo "<p style='color: red;'>❌ Falha na query de teste</p>";
    }
    
} catch (Exception $e) {
    $problemas[] = "❌ Erro de conexão: " . $e->getMessage();
    echo "<p style='color: red;'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

// 4. Verificar classes principais
echo "<h2>4. Verificação de Classes</h2>";

try {
    require_once '../src/init.php';
    
    $classes = ['Cliente', 'Pet', 'Agendamento', 'Auth'];
    foreach ($classes as $classe) {
        if (class_exists($classe)) {
            $sucessos[] = "✅ Classe $classe carregada";
            echo "<p style='color: green;'>✅ Classe $classe carregada</p>";
        } else {
            $problemas[] = "❌ Classe $classe não encontrada";
            echo "<p style='color: red;'>❌ Classe $classe não encontrada</p>";
        }
    }
    
} catch (Exception $e) {
    $problemas[] = "❌ Erro ao carregar classes: " . $e->getMessage();
    echo "<p style='color: red;'>❌ Erro ao carregar classes: " . $e->getMessage() . "</p>";
}

// 5. Verificar diretórios
echo "<h2>5. Verificação de Diretórios</h2>";

$diretorios = [
    '../logs' => 'Logs',
    '../uploads' => 'Uploads',
    '../cache' => 'Cache',
    '../backups' => 'Backups'
];

foreach ($diretorios as $dir => $nome) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $sucessos[] = "✅ Diretório $nome OK";
            echo "<p style='color: green;'>✅ Diretório $nome OK</p>";
        } else {
            $problemas[] = "❌ Diretório $nome não é gravável";
            echo "<p style='color: red;'>❌ Diretório $nome não é gravável</p>";
        }
    } else {
        $problemas[] = "❌ Diretório $nome não existe";
        echo "<p style='color: red;'>❌ Diretório $nome não existe</p>";
    }
}

// 6. Verificar sessão
echo "<h2>6. Verificação de Sessão</h2>";

if (session_status() === PHP_SESSION_ACTIVE) {
    $sucessos[] = "✅ Sessão ativa";
    echo "<p style='color: green;'>✅ Sessão ativa</p>";
} else {
    $sucessos[] = "ℹ️ Sessão não iniciada (normal se não logado)";
    echo "<p style='color: blue;'>ℹ️ Sessão não iniciada (normal se não logado)</p>";
}

// Resumo
echo "<h2>📊 Resumo</h2>";
echo "<p><strong>Sucessos:</strong> " . count($sucessos) . "</p>";
echo "<p><strong>Problemas:</strong> " . count($problemas) . "</p>";

if (count($problemas) === 0) {
    echo "<div style='background: #d1fae5; border: 1px solid #10b981; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #065f46; margin: 0;'>🎉 Sistema OK!</h3>";
    echo "<p style='color: #065f46; margin: 10px 0 0 0;'>Todos os componentes estão funcionando corretamente.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #fef2f2; border: 1px solid #ef4444; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #991b1b; margin: 0;'>⚠️ Problemas Encontrados</h3>";
    echo "<ul style='color: #991b1b; margin: 10px 0 0 0;'>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<h2>🔧 Ações Recomendadas</h2>";
echo "<ul>";
if (count($problemas) > 0) {
    echo "<li><a href='configurar-banco.php'>Configurar banco de dados</a></li>";
    echo "<li><a href='diagnostico-banco.php'>Executar diagnóstico completo</a></li>";
    echo "<li><a href='verificar-todos-arquivos.php'>Verificar todos os arquivos</a></li>";
} else {
    echo "<li><a href='index.php'>Acessar o sistema</a></li>";
    echo "<li><a href='login.php'>Fazer login</a></li>";
}
echo "</ul>";

echo "<p><a href='index.php'>← Voltar ao sistema</a></p>";
?>
