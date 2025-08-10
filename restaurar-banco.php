<?php
/**
 * Restaurar Banco de Dados
 * Sistema Bichos do Bairro
 */

echo "=== RESTAURAÇÃO DO BANCO DE DADOS ===\n\n";

// Verificar se o arquivo de backup foi fornecido
if ($argc < 2) {
    echo "❌ ERRO: Arquivo de backup não especificado\n";
    echo "Uso: php restaurar-banco.php arquivo_backup.sql\n";
    echo "Exemplo: php restaurar-banco.php backups/backup_completo_2025-07-19_20-19-55.sql\n\n";
    exit(1);
}

$backupFile = $argv[1];

if (!file_exists($backupFile)) {
    echo "❌ ERRO: Arquivo de backup não encontrado: $backupFile\n";
    exit(1);
}

echo "1. Verificando arquivo de backup...\n";
echo "✅ Arquivo: $backupFile\n";

$fileSize = filesize($backupFile);
$fileSizeMB = round($fileSize / 1024 / 1024, 2);
echo "✅ Tamanho: $fileSizeMB MB\n\n";

// Configurações do banco (AJUSTAR PARA O NOVO SERVIDOR)
echo "2. Configurando conexão com banco...\n";

$dbConfig = [
    'host' => 'localhost', // AJUSTAR
    'name' => 'bichosdobairro', // AJUSTAR
    'user' => 'root', // AJUSTAR
    'pass' => '', // AJUSTAR
    'charset' => 'utf8mb4'
];

echo "Host: {$dbConfig['host']}\n";
echo "Database: {$dbConfig['name']}\n";
echo "User: {$dbConfig['user']}\n\n";

// Perguntar se quer continuar
echo "⚠️  ATENÇÃO: Esta operação irá:\n";
echo "   - Apagar todas as tabelas existentes\n";
echo "   - Restaurar o backup completo\n";
echo "   - Substituir todos os dados\n\n";

echo "Deseja continuar? (s/N): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) !== 's' && strtolower($response) !== 'sim' && strtolower($response) !== 'y' && strtolower($response) !== 'yes') {
    echo "❌ Restauração cancelada pelo usuário\n";
    exit(0);
}

echo "\n3. Conectando ao banco de dados...\n";

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Conexão estabelecida\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
    echo "\nVerifique:\n";
    echo "1. Se o banco de dados existe\n";
    echo "2. Se as credenciais estão corretas\n";
    echo "3. Se o usuário tem permissões\n";
    exit(1);
}

echo "4. Iniciando restauração...\n";

// Ler o arquivo de backup
$sql = file_get_contents($backupFile);
if (!$sql) {
    echo "❌ Erro ao ler arquivo de backup\n";
    exit(1);
}

// Dividir o SQL em comandos
$commands = array_filter(array_map('trim', explode(';', $sql)));

$totalCommands = count($commands);
$commandsExecutados = 0;
$erros = [];

echo "   Total de comandos: $totalCommands\n\n";

foreach ($commands as $command) {
    if (!empty($command)) {
        $commandsExecutados++;
        
        try {
            $pdo->exec($command);
            
            if ($commandsExecutados % 10 == 0) {
                echo "   Progresso: $commandsExecutados/$totalCommands comandos executados\n";
            }
            
        } catch (Exception $e) {
            $erros[] = "Comando $commandsExecutados: " . $e->getMessage();
            
            // Continuar mesmo com erros (alguns podem ser normais)
            if ($commandsExecutados % 10 == 0) {
                echo "   ⚠️  Erro no comando $commandsExecutados (continuando...)\n";
            }
        }
    }
}

echo "\n5. Restauração concluída!\n";
echo "✅ Comandos executados: $commandsExecutados/$totalCommands\n";

if (!empty($erros)) {
    echo "⚠️  Erros encontrados: " . count($erros) . "\n";
    foreach ($erros as $erro) {
        echo "   - $erro\n";
    }
}

echo "\n6. Verificando restauração...\n";

try {
    // Verificar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ Tabelas encontradas: " . count($tabelas) . "\n";
    
    $tabelasEsperadas = [
        'usuarios', 'niveis_acesso', 'clientes', 'telefones', 'pets',
        'agendamentos', 'agendamentos_recorrentes', 'agendamentos_recorrentes_ocorrencias',
        'logs_atividade', 'logs_login', 'notificacoes'
    ];
    
    $tabelasFaltando = array_diff($tabelasEsperadas, $tabelas);
    if (empty($tabelasFaltando)) {
        echo "✅ Todas as tabelas principais estão presentes\n";
    } else {
        echo "⚠️  Tabelas faltando: " . implode(', ', $tabelasFaltando) . "\n";
    }
    
    // Verificar dados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    $usuarios = $result['total'];
    echo "✅ Usuários: $usuarios\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
    $result = $stmt->fetch();
    $clientes = $result['total'];
    echo "✅ Clientes: $clientes\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pets");
    $result = $stmt->fetch();
    $pets = $result['total'];
    echo "✅ Pets: $pets\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
    $result = $stmt->fetch();
    $agendamentos = $result['total'];
    echo "✅ Agendamentos: $agendamentos\n";
    
} catch (Exception $e) {
    echo "❌ Erro na verificação: " . $e->getMessage() . "\n";
}

echo "\n=== RESUMO DA RESTAURAÇÃO ===\n";
echo "✅ Arquivo restaurado: " . basename($backupFile) . "\n";
echo "✅ Comandos executados: $commandsExecutados\n";
echo "✅ Tabelas criadas: " . count($tabelas) . "\n";
echo "✅ Dados importados: $usuarios usuários, $clientes clientes, $pets pets, $agendamentos agendamentos\n";

if (empty($erros)) {
    echo "✅ Status: RESTAURAÇÃO CONCLUÍDA COM SUCESSO!\n\n";
} else {
    echo "⚠️  Status: RESTAURAÇÃO CONCLUÍDA COM AVISOS\n\n";
}

echo "=== PRÓXIMOS PASSOS ===\n";
echo "1. Configure o arquivo .env com as credenciais do novo banco\n";
echo "2. Teste o login do sistema\n";
echo "3. Verifique as funcionalidades principais\n";
echo "4. Altere as senhas dos usuários se necessário\n\n";

echo "🔑 CREDENCIAIS PADRÃO:\n";
echo "Email: admin@bichosdobairro.com\n";
echo "Senha: admin123\n\n";

echo "🎉 BANCO RESTAURADO COM SUCESSO! 🎉\n";
?> 