<?php
/**
 * Script de Configuração Automática do Ambiente
 * Sistema Bichos do Bairro
 */

echo "<h1>🔧 Configuração do Ambiente - Bichos do Bairro</h1>";

// Verificar se é Windows ou Linux
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$separator = $isWindows ? '\\' : '/';

echo "<h2>1. Verificando Requisitos do Sistema</h2>";

// Verificar versão do PHP
$phpVersion = PHP_VERSION;
$minPhpVersion = '7.4.0';
echo "<p>PHP Version: $phpVersion " . (version_compare($phpVersion, $minPhpVersion, '>=') ? '✅' : '❌') . "</p>";

// Verificar extensões necessárias
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>Extensão $ext: " . ($loaded ? '✅' : '❌') . "</p>";
}

// Verificar permissões de diretórios
echo "<h2>2. Verificando Permissões de Diretórios</h2>";

$directories = [
    'logs',
    'backups',
    'cache',
    'uploads'
];

foreach ($directories as $dir) {
    $path = __DIR__ . $separator . $dir;
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "<p>✅ Diretório $dir criado com sucesso</p>";
        } else {
            echo "<p>❌ Erro ao criar diretório $dir</p>";
        }
    } else {
        echo "<p>✅ Diretório $dir já existe</p>";
    }
    
    // Verificar permissões de escrita
    if (is_writable($path)) {
        echo "<p>✅ Permissão de escrita em $dir</p>";
    } else {
        echo "<p>❌ Sem permissão de escrita em $dir</p>";
    }
}

echo "<h2>3. Configurando Arquivo .env</h2>";

$envExample = __DIR__ . $separator . 'env.example';
$envFile = __DIR__ . $separator . '.env';

if (file_exists($envExample)) {
    if (!file_exists($envFile)) {
        if (copy($envExample, $envFile)) {
            echo "<p>✅ Arquivo .env criado a partir do exemplo</p>";
        } else {
            echo "<p>❌ Erro ao criar arquivo .env</p>";
        }
    } else {
        echo "<p>✅ Arquivo .env já existe</p>";
    }
} else {
    echo "<p>⚠️ Arquivo env.example não encontrado</p>";
}

echo "<h2>4. Testando Conexão com Banco de Dados</h2>";

try {
    require_once __DIR__ . $separator . 'src' . $separator . 'init.php';
    
    global $pdo;
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "<p>✅ Conexão com banco de dados: OK</p>";
        
        // Verificar tabelas
        $tables = ['clientes', 'pets', 'agendamentos', 'telefones'];
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                $exists = $stmt->fetch();
                echo "<p>Tabela $table: " . ($exists ? '✅' : '❌') . "</p>";
            } catch (Exception $e) {
                echo "<p>Tabela $table: ❌ Erro ao verificar</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro na conexão com banco: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Configurando Composer</h2>";

$composerFile = __DIR__ . $separator . 'composer.json';
if (file_exists($composerFile)) {
    echo "<p>✅ composer.json encontrado</p>";
    
    // Verificar se vendor existe
    $vendorDir = __DIR__ . $separator . 'vendor';
    if (is_dir($vendorDir)) {
        echo "<p>✅ Diretório vendor existe</p>";
    } else {
        echo "<p>⚠️ Diretório vendor não encontrado. Execute: composer install</p>";
    }
} else {
    echo "<p>❌ composer.json não encontrado</p>";
}

echo "<h2>6. Configurando Servidor Web</h2>";

// Verificar se está rodando em servidor web
if (php_sapi_name() !== 'cli') {
    echo "<p>✅ Executando em servidor web</p>";
    
    // Verificar se mod_rewrite está disponível (para Apache)
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        if (in_array('mod_rewrite', $modules)) {
            echo "<p>✅ mod_rewrite está ativo</p>";
        } else {
            echo "<p>⚠️ mod_rewrite não está ativo (pode ser necessário para URLs amigáveis)</p>";
        }
    }
} else {
    echo "<p>ℹ️ Executando via linha de comando</p>";
}

echo "<h2>7. Configurações de Segurança</h2>";

// Verificar se arquivos sensíveis estão protegidos
$sensitiveFiles = ['.env', 'composer.lock', 'config_agenda.json'];
foreach ($sensitiveFiles as $file) {
    $filePath = __DIR__ . $separator . $file;
    if (file_exists($filePath)) {
        $perms = fileperms($filePath);
        $perms = substr(sprintf('%o', $perms), -4);
        echo "<p>Permissões do $file: $perms " . ($perms <= '0644' ? '✅' : '⚠️') . "</p>";
    }
}

echo "<h2>8. Testando Funcionalidades do Sistema</h2>";

try {
    // Testar classes principais
    $classes = ['Config', 'Utils', 'Cliente', 'Pet', 'Agendamento'];
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "<p>✅ Classe $class carregada</p>";
        } else {
            echo "<p>❌ Classe $class não encontrada</p>";
        }
    }
    
    // Testar cache
    if (class_exists('Cache')) {
        Cache::set('setup_test', 'ok', 60);
        $test = Cache::get('setup_test');
        echo "<p>Cache: " . ($test === 'ok' ? '✅' : '❌') . "</p>";
    }
    
    // Testar logs
    if (class_exists('Logger')) {
        Logger::info('Teste de configuração', ['setup' => 'running']);
        echo "<p>✅ Sistema de logs funcionando</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro nos testes: " . $e->getMessage() . "</p>";
}

echo "<h2>9. Recomendações de Configuração</h2>";

echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Para Desenvolvimento:</h3>";
echo "<ul>";
echo "<li>Configure seu servidor web (Apache/Nginx) para apontar para a pasta 'public'</li>";
echo "<li>Certifique-se de que o mod_rewrite está ativo (Apache)</li>";
echo "<li>Configure o arquivo .env com suas configurações locais</li>";
echo "<li>Execute 'composer install' se ainda não fez</li>";
echo "</ul>";

echo "<h3>Para Produção:</h3>";
echo "<ul>";
echo "<li>Defina APP_ENV=production no .env</li>";
echo "<li>Defina APP_DEBUG=false no .env</li>";
echo "<li>Configure um servidor web adequado (Apache/Nginx)</li>";
echo "<li>Configure SSL/HTTPS</li>";
echo "<li>Configure backup automático do banco de dados</li>";
echo "<li>Monitore os logs regularmente</li>";
echo "</ul>";
echo "</div>";

echo "<h2>10. Próximos Passos</h2>";

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<p><strong>✅ Configuração concluída!</strong></p>";
echo "<p>Agora você pode:</p>";
echo "<ul>";
echo "<li><a href='public/dashboard.php'>Acessar o Dashboard</a></li>";
echo "<li><a href='public/teste-melhorias.php'>Executar Testes</a></li>";
echo "<li><a href='public/admin.php'>Acessar Administração</a></li>";
echo "<li><a href='public/corrigir-banco.php'>Executar Correções do Banco</a></li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><small>Script de configuração executado em: " . date('d/m/Y H:i:s') . "</small></p>";
?> 