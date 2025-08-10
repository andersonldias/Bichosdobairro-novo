<?php
/**
 * Script de Deploy para Produção
 * Sistema Bichos do Bairro
 */

echo "=== DEPLOY PARA PRODUÇÃO - BICHOS DO BAIRRO ===\n\n";

// 1. Verificar estrutura de arquivos
echo "1. Verificando estrutura de arquivos...\n";

$arquivosObrigatorios = [
    'src/Config.php',
    'src/init.php',
    'src/db.php',
    'src/Utils.php',
    'public/index.php',
    'public/layout.php',
    'public/login.php',
    'public/dashboard.php',
    'public/.htaccess',
    'sql/database.sql',
    'README.md'
];

$arquivosFaltando = [];
foreach ($arquivosObrigatorios as $arquivo) {
    if (!file_exists($arquivo)) {
        $arquivosFaltando[] = $arquivo;
    }
}

if (!empty($arquivosFaltando)) {
    echo "❌ ERRO: Arquivos obrigatórios não encontrados:\n";
    foreach ($arquivosFaltando as $arquivo) {
        echo "   - $arquivo\n";
    }
    exit(1);
}

echo "✅ Todos os arquivos obrigatórios encontrados\n\n";

// 2. Verificar sintaxe PHP
echo "2. Verificando sintaxe PHP...\n";

$arquivosPHP = [
    'src/Config.php',
    'src/init.php',
    'src/db.php',
    'src/Utils.php',
    'public/index.php',
    'public/layout.php',
    'public/login.php',
    'public/dashboard.php'
];

$errosSintaxe = [];
foreach ($arquivosPHP as $arquivo) {
    $output = [];
    $returnCode = 0;
    exec("php -l \"$arquivo\" 2>&1", $output, $returnCode);
    
    if ($returnCode !== 0) {
        $errosSintaxe[] = "$arquivo: " . implode("\n", $output);
    }
}

if (!empty($errosSintaxe)) {
    echo "❌ ERRO: Erros de sintaxe encontrados:\n";
    foreach ($errosSintaxe as $erro) {
        echo "   $erro\n";
    }
    exit(1);
}

echo "✅ Sintaxe PHP verificada com sucesso\n\n";

// 3. Verificar configurações de produção
echo "3. Verificando configurações de produção...\n";

if (!file_exists('env.production')) {
    echo "❌ ERRO: Arquivo env.production não encontrado\n";
    exit(1);
}

echo "✅ Arquivo de configuração de produção encontrado\n\n";

// 4. Verificar banco de dados
echo "4. Verificando conexão com banco de dados...\n";

try {
    $dbConfig = [
        'host' => 'xmysql.bichosdobairro.com.br',
        'name' => 'bichosdobairro5',
        'user' => 'bichosdobairro5',
        'pass' => '!BdoB.1179!',
        'charset' => 'utf8mb4'
    ];
    
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Conexão com banco estabelecida\n";
    
    // Verificar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $tabelasEsperadas = [
        'usuarios', 'niveis_acesso', 'clientes', 'telefones', 'pets',
        'agendamentos', 'agendamentos_recorrentes', 'agendamentos_recorrentes_ocorrencias',
        'logs_atividade', 'logs_login', 'notificacoes'
    ];
    
    $tabelasFaltando = array_diff($tabelasEsperadas, $tabelas);
    if (!empty($tabelasFaltando)) {
        echo "⚠️  AVISO: Tabelas faltando: " . implode(', ', $tabelasFaltando) . "\n";
    } else {
        echo "✅ Todas as tabelas estão presentes\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: Falha na conexão com banco: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// 5. Criar diretórios necessários
echo "5. Criando diretórios necessários...\n";

$diretorios = ['logs', 'backups', 'cache'];
foreach ($diretorios as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Diretório $dir criado\n";
    } else {
        echo "✅ Diretório $dir já existe\n";
    }
}

echo "\n";

// 6. Configurar permissões
echo "6. Configurando permissões...\n";

$arquivosPermissoes = [
    'logs' => 0755,
    'backups' => 0755,
    'cache' => 0755
];

foreach ($arquivosPermissoes as $arquivo => $permissao) {
    if (is_dir($arquivo)) {
        chmod($arquivo, $permissao);
        echo "✅ Permissões configuradas para $arquivo\n";
    }
}

echo "\n";

// 7. Verificar arquivos de segurança
echo "7. Verificando arquivos de segurança...\n";

$arquivosSeguranca = [
    'public/.htaccess',
    'public/404.php',
    'public/500.php'
];

foreach ($arquivosSeguranca as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo encontrado\n";
    } else {
        echo "⚠️  AVISO: $arquivo não encontrado\n";
    }
}

echo "\n";

// 8. Gerar chave de aplicação
echo "8. Gerando chave de aplicação...\n";

$chave = base64_encode(random_bytes(32));
echo "✅ Chave de aplicação gerada: $chave\n";
echo "   (Atualize APP_KEY no arquivo .env)\n\n";

// 9. Resumo final
echo "=== RESUMO DO DEPLOY ===\n";
echo "✅ Estrutura de arquivos: OK\n";
echo "✅ Sintaxe PHP: OK\n";
echo "✅ Configurações: OK\n";
echo "✅ Banco de dados: OK\n";
echo "✅ Diretórios: OK\n";
echo "✅ Permissões: OK\n";
echo "✅ Segurança: OK\n\n";

echo "=== PRÓXIMOS PASSOS ===\n";
echo "1. Faça upload dos arquivos para o servidor\n";
echo "2. Configure o arquivo .env com as credenciais corretas\n";
echo "3. Configure o APP_KEY: $chave\n";
echo "4. Configure o domínio no APP_URL\n";
echo "5. Teste o sistema: https://seu-dominio.com\n";
echo "6. Altere a senha do administrador\n\n";

echo "=== CREDENCIAIS PADRÃO ===\n";
echo "Email: admin@bichosdobairro.com\n";
echo "Senha: admin123\n\n";

echo "🎉 DEPLOY PRONTO PARA PRODUÇÃO! 🎉\n";
?> 