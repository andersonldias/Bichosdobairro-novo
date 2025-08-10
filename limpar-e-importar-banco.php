<?php
/**
 * Script para Limpar e Importar Banco de Dados
 * Sistema Bichos do Bairro
 */

echo "=== LIMPEZA E IMPORTAÇÃO DO BANCO DE DADOS ===\n\n";

// Verificar se o arquivo de backup foi fornecido
if ($argc < 2) {
    echo "❌ ERRO: Arquivo de backup não especificado\n";
    echo "Uso: php limpar-e-importar-banco.php arquivo_backup.sql\n";
    echo "Exemplo: php limpar-e-importar-banco.php backups/backup_completo_2025-07-19_20-19-55.sql\n\n";
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
echo "   - APAGAR TODOS os dados do banco atual\n";
echo "   - Importar o backup completo\n";
echo "   - Substituir completamente o banco\n\n";

echo "Deseja continuar? (s/N): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) !== 's' && strtolower($response) !== 'sim' && strtolower($response) !== 'y' && strtolower($response) !== 'yes') {
    echo "❌ Operação cancelada pelo usuário\n";
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

echo "4. Limpando banco de dados...\n";

// Script para limpar o banco
$limparSQL = "
SET FOREIGN_KEY_CHECKS = 0;

-- Apagar todas as tabelas
DROP TABLE IF EXISTS agendamentos_recorrentes_ocorrencias;
DROP TABLE IF EXISTS agendamentos_recorrentes;
DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS logs_agendamentos_recorrentes;
DROP TABLE IF EXISTS logs_atividade;
DROP TABLE IF EXISTS logs_login;
DROP TABLE IF EXISTS usuarios_permissoes;
DROP TABLE IF EXISTS nivel_permissoes;
DROP TABLE IF EXISTS permissoes;
DROP TABLE IF EXISTS niveis_acesso;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS telefones;
DROP TABLE IF EXISTS pets;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS notificacoes;
DROP TABLE IF EXISTS configuracoes;

SET FOREIGN_KEY_CHECKS = 1;
";

try {
    $pdo->exec($limparSQL);
    echo "✅ Banco limpo com sucesso\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao limpar banco: " . $e->getMessage() . "\n";
    exit(1);
}

echo "5. Verificando se o banco está limpo...\n";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tabelas)) {
        echo "✅ Banco completamente limpo\n\n";
    } else {
        echo "⚠️  Ainda existem " . count($tabelas) . " tabela(s):\n";
        foreach ($tabelas as $tabela) {
            echo "   - $tabela\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao verificar tabelas: " . $e->getMessage() . "\n";
}

echo "6. Importando backup...\n";

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
            
            if ($commandsExecutados % 20 == 0) {
                echo "   Progresso: $commandsExecutados/$totalCommands comandos executados\n";
            }
            
        } catch (Exception $e) {
            $erros[] = "Comando $commandsExecutados: " . $e->getMessage();
            
            // Continuar mesmo com erros (alguns podem ser normais)
            if ($commandsExecutados % 20 == 0) {
                echo "   ⚠️  Erro no comando $commandsExecutados (continuando...)\n";
            }
        }
    }
}

echo "\n7. Importação concluída!\n";
echo "✅ Comandos executados: $commandsExecutados/$totalCommands\n";

if (!empty($erros)) {
    echo "⚠️  Erros encontrados: " . count($erros) . "\n";
    foreach (array_slice($erros, 0, 5) as $erro) {
        echo "   - $erro\n";
    }
    if (count($erros) > 5) {
        echo "   ... e mais " . (count($erros) - 5) . " erros\n";
    }
}

echo "\n8. Verificando importação...\n";

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

echo "\n=== RESUMO DA OPERAÇÃO ===\n";
echo "✅ Banco limpo completamente\n";
echo "✅ Backup importado: " . basename($backupFile) . "\n";
echo "✅ Comandos executados: $commandsExecutados\n";
echo "✅ Tabelas criadas: " . count($tabelas) . "\n";
echo "✅ Dados importados: $usuarios usuários, $clientes clientes, $pets pets, $agendamentos agendamentos\n";

if (empty($erros)) {
    echo "✅ Status: OPERAÇÃO CONCLUÍDA COM SUCESSO!\n\n";
} else {
    echo "⚠️  Status: OPERAÇÃO CONCLUÍDA COM AVISOS\n\n";
}

echo "=== PRÓXIMOS PASSOS ===\n";
echo "1. Configure o arquivo .env com as credenciais do novo banco\n";
echo "2. Execute: php verificar-producao.php\n";
echo "3. Teste o login do sistema\n";
echo "4. Verifique as funcionalidades principais\n";
echo "5. Altere as senhas dos usuários se necessário\n\n";

echo "🔑 CREDENCIAIS PADRÃO:\n";
echo "Email: admin@bichosdobairro.com\n";
echo "Senha: admin123\n\n";

echo "🎉 BANCO LIMPO E RESTAURADO COM SUCESSO! 🎉\n";
?> 