<?php
/**
 * Teste de Compatibilidade - Hospedagem Compartilhada
 * Sistema Bichos do Bairro
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Teste de Compatibilidade - Bichos do Bairro</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; font-weight: bold; }";
echo "h1 { color: #333; text-align: center; }";
echo "h2 { color: #555; border-bottom: 2px solid #eee; padding-bottom: 10px; }";
echo ".test-item { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; }";
echo ".summary { background: #e9ecef; padding: 15px; border-radius: 4px; margin: 20px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Teste de Compatibilidade</h1>";
echo "<h2>Sistema Bichos do Bairro - Hospedagem Compartilhada</h2>";

$tests = [];
$totalTests = 0;
$passedTests = 0;

// Teste 1: Versão do PHP
echo "<h2>1. Verificação do PHP</h2>";
$totalTests++;
$phpVersion = PHP_VERSION;
$minVersion = '7.4.0';

if (version_compare($phpVersion, $minVersion, '>=')) {
    echo "<div class='test-item'>";
    echo "<span class='success'>✅ PHP $phpVersion - Compatível</span>";
    echo "</div>";
    $passedTests++;
    $tests['php'] = true;
} else {
    echo "<div class='test-item'>";
    echo "<span class='error'>❌ PHP $phpVersion - Versão mínima requerida: $minVersion</span>";
    echo "</div>";
    $tests['php'] = false;
}

// Teste 2: Extensões necessárias
echo "<h2>2. Extensões PHP</h2>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl'];
foreach ($requiredExtensions as $ext) {
    $totalTests++;
    if (extension_loaded($ext)) {
        echo "<div class='test-item'>";
        echo "<span class='success'>✅ Extensão $ext - Disponível</span>";
        echo "</div>";
        $passedTests++;
        $tests["ext_$ext"] = true;
    } else {
        echo "<div class='test-item'>";
        echo "<span class='error'>❌ Extensão $ext - Não disponível</span>";
        echo "</div>";
        $tests["ext_$ext"] = false;
    }
}

// Teste 3: Permissões de diretórios
echo "<h2>3. Permissões de Diretórios</h2>";
$directories = ['logs', 'backups', 'cache', 'uploads'];
foreach ($directories as $dir) {
    $totalTests++;
    $path = "../$dir";
    
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "<div class='test-item'>";
            echo "<span class='success'>✅ Diretório $dir - Criado com sucesso</span>";
            echo "</div>";
            $passedTests++;
            $tests["dir_$dir"] = true;
        } else {
            echo "<div class='test-item'>";
            echo "<span class='error'>❌ Diretório $dir - Erro ao criar</span>";
            echo "</div>";
            $tests["dir_$dir"] = false;
        }
    } else {
        if (is_writable($path)) {
            echo "<div class='test-item'>";
            echo "<span class='success'>✅ Diretório $dir - Existe e tem permissão de escrita</span>";
            echo "</div>";
            $passedTests++;
            $tests["dir_$dir"] = true;
        } else {
            echo "<div class='test-item'>";
            echo "<span class='warning'>⚠️ Diretório $dir - Existe mas sem permissão de escrita</span>";
            echo "</div>";
            $tests["dir_$dir"] = false;
        }
    }
}

// Teste 4: Carregamento do sistema
echo "<h2>4. Carregamento do Sistema</h2>";

// Teste 4.1: Arquivo de configuração
$totalTests++;
if (file_exists('../src/Config.php')) {
    echo "<div class='test-item'>";
    echo "<span class='success'>✅ Arquivo Config.php - Encontrado</span>";
    echo "</div>";
    $passedTests++;
    $tests['config_file'] = true;
} else {
    echo "<div class='test-item'>";
    echo "<span class='error'>❌ Arquivo Config.php - Não encontrado</span>";
    echo "</div>";
    $tests['config_file'] = false;
}

// Teste 4.2: Carregar configurações
$totalTests++;
try {
    require_once '../src/Config.php';
    Config::load();
    echo "<div class='test-item'>";
    echo "<span class='success'>✅ Configurações - Carregadas com sucesso</span>";
    echo "</div>";
    $passedTests++;
    $tests['config_load'] = true;
} catch (Exception $e) {
    echo "<div class='test-item'>";
    echo "<span class='error'>❌ Configurações - Erro ao carregar: " . $e->getMessage() . "</span>";
    echo "</div>";
    $tests['config_load'] = false;
}

// Teste 5: Conexão com banco de dados
echo "<h2>5. Conexão com Banco de Dados</h2>";
$totalTests++;
try {
    require_once '../src/db.php';
    global $pdo;
    
    if ($pdo instanceof PDO) {
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result) {
            echo "<div class='test-item'>";
            echo "<span class='success'>✅ Conexão com banco - Estabelecida com sucesso</span>";
            echo "</div>";
            $passedTests++;
            $tests['database'] = true;
        } else {
            echo "<div class='test-item'>";
            echo "<span class='error'>❌ Conexão com banco - Falha na consulta de teste</span>";
            echo "</div>";
            $tests['database'] = false;
        }
    } else {
        echo "<div class='test-item'>";
        echo "<span class='error'>❌ Conexão com banco - Objeto PDO não criado</span>";
        echo "</div>";
        $tests['database'] = false;
    }
} catch (Exception $e) {
    echo "<div class='test-item'>";
    echo "<span class='error'>❌ Conexão com banco - Erro: " . $e->getMessage() . "</span>";
    echo "</div>";
    $tests['database'] = false;
}

// Teste 6: Verificação de tabelas
echo "<h2>6. Estrutura do Banco</h2>";
$requiredTables = ['clientes', 'pets', 'agendamentos', 'telefones'];
foreach ($requiredTables as $table) {
    $totalTests++;
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "<div class='test-item'>";
            echo "<span class='success'>✅ Tabela $table - Existe</span>";
            echo "</div>";
            $passedTests++;
            $tests["table_$table"] = true;
        } else {
            echo "<div class='test-item'>";
            echo "<span class='warning'>⚠️ Tabela $table - Não encontrada</span>";
            echo "</div>";
            $tests["table_$table"] = false;
        }
    } catch (Exception $e) {
        echo "<div class='test-item'>";
        echo "<span class='error'>❌ Tabela $table - Erro ao verificar: " . $e->getMessage() . "</span>";
        echo "</div>";
        $tests["table_$table"] = false;
    }
}

// Teste 7: Funcionalidades do sistema
echo "<h2>7. Funcionalidades do Sistema</h2>";

// Teste 7.1: Funções helper
$totalTests++;
try {
    $testDate = formatDate('2024-01-15');
    $testCurrency = formatCurrency(123.45);
    $testPhone = formatPhone('11987654321');
    
    if ($testDate && $testCurrency && $testPhone) {
        echo "<div class='test-item'>";
        echo "<span class='success'>✅ Funções helper - Funcionando corretamente</span>";
        echo "</div>";
        $passedTests++;
        $tests['helper_functions'] = true;
    } else {
        echo "<div class='test-item'>";
        echo "<span class='error'>❌ Funções helper - Erro nas funções</span>";
        echo "</div>";
        $tests['helper_functions'] = false;
    }
} catch (Exception $e) {
    echo "<div class='test-item'>";
    echo "<span class='error'>❌ Funções helper - Erro: " . $e->getMessage() . "</span>";
    echo "</div>";
    $tests['helper_functions'] = false;
}

// Teste 7.2: Sistema de logs
$totalTests++;
try {
    logInfo('Teste de compatibilidade executado', ['test' => 'compatibility']);
    $logFile = '../logs/app.log';
    
    if (file_exists($logFile)) {
        echo "<div class='test-item'>";
        echo "<span class='success'>✅ Sistema de logs - Funcionando</span>";
        echo "</div>";
        $passedTests++;
        $tests['logging'] = true;
    } else {
        echo "<div class='test-item'>";
        echo "<span class='warning'>⚠️ Sistema de logs - Arquivo não criado</span>";
        echo "</div>";
        $tests['logging'] = false;
    }
} catch (Exception $e) {
    echo "<div class='test-item'>";
    echo "<span class='error'>❌ Sistema de logs - Erro: " . $e->getMessage() . "</span>";
    echo "</div>";
    $tests['logging'] = false;
}

// Resumo dos testes
echo "<h2>📊 Resumo dos Testes</h2>";
echo "<div class='summary'>";
echo "<p><strong>Total de testes:</strong> $totalTests</p>";
echo "<p><strong>Testes aprovados:</strong> $passedTests</p>";
echo "<p><strong>Testes reprovados:</strong> " . ($totalTests - $passedTests) . "</p>";
echo "<p><strong>Taxa de sucesso:</strong> " . round(($passedTests / $totalTests) * 100, 1) . "%</p>";

if ($passedTests === $totalTests) {
    echo "<p class='success'>🎉 Sistema 100% compatível com hospedagem compartilhada!</p>";
} elseif ($passedTests >= ($totalTests * 0.8)) {
    echo "<p class='warning'>⚠️ Sistema compatível com pequenos ajustes necessários.</p>";
} else {
    echo "<p class='error'>❌ Sistema não compatível. Verifique os requisitos.</p>";
}
echo "</div>";

// Recomendações
echo "<h2>💡 Recomendações</h2>";
if (!$tests['php']) {
    echo "<div class='test-item'>";
    echo "<span class='error'>❌ Atualize o PHP para versão 7.4 ou superior</span>";
    echo "</div>";
}

foreach ($requiredExtensions as $ext) {
    if (!$tests["ext_$ext"]) {
        echo "<div class='test-item'>";
        echo "<span class='error'>❌ Solicite ao provedor de hospedagem para habilitar a extensão $ext</span>";
        echo "</div>";
    }
}

if (!$tests['database']) {
    echo "<div class='test-item'>";
    echo "<span class='error'>❌ Verifique as configurações do banco de dados no arquivo .env</span>";
    echo "</div>";
}

if ($passedTests === $totalTests) {
    echo "<div class='test-item'>";
    echo "<span class='success'>✅ Sistema pronto para uso em hospedagem compartilhada!</span>";
    echo "</div>";
    echo "<div class='test-item'>";
    echo "<span class='info'>ℹ️ Você pode agora fazer upload dos arquivos para sua hospedagem</span>";
    echo "</div>";
}

echo "<h2>🔗 Próximos Passos</h2>";
echo "<div class='test-item'>";
echo "<a href='dashboard.php' style='color: #007bff; text-decoration: none;'>→ Acessar Dashboard</a>";
echo "</div>";
echo "<div class='test-item'>";
echo "<a href='clientes.php' style='color: #007bff; text-decoration: none;'>→ Gerenciar Clientes</a>";
echo "</div>";
echo "<div class='test-item'>";
echo "<a href='pets.php' style='color: #007bff; text-decoration: none;'>→ Gerenciar Pets</a>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 