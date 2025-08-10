<?php
/**
 * VERIFICAÇÃO FINAL PARA HOSPEDAGEM
 * Script para verificar se o sistema está pronto para upload
 */

echo "<h1>🔍 VERIFICAÇÃO FINAL PARA HOSPEDAGEM</h1>";
echo "<h2>Sistema Bichos do Bairro</h2>";
echo "<hr>";

$erros = [];
$avisos = [];
$sucessos = [];

// 1. Verificar arquivos essenciais
echo "<h3>📁 Verificando arquivos essenciais...</h3>";

$arquivos_essenciais = [
    'public/index.php',
    'public/login.php',
    'public/dashboard.php',
    'public/admin.php',
    'public/agendamentos.php',
    'public/clientes.php',
    'public/pets.php',
    'public/.htaccess',
    'src/Config.php',
    'src/db.php',
    'src/Auth.php',
    'vendor/autoload.php',
    'sql/database.sql',
    'env.example',
    'composer.json'
];

foreach ($arquivos_essenciais as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo - OK<br>";
        $sucessos[] = $arquivo;
    } else {
        echo "❌ $arquivo - FALTANDO<br>";
        $erros[] = $arquivo;
    }
}

// 2. Verificar configuração do banco
echo "<h3>🗄️ Verificando configuração do banco...</h3>";

if (file_exists('src/Config.php')) {
    require_once 'src/Config.php';
    
    // Verificar se as constantes estão definidas
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            echo "✅ Conexão com banco - OK<br>";
            $sucessos[] = 'Conexão com banco';
            
            // Verificar tabelas essenciais
            $tabelas = ['usuarios', 'niveis_acesso', 'clientes', 'pets', 'agendamentos'];
            foreach ($tabelas as $tabela) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
                if ($stmt->rowCount() > 0) {
                    echo "✅ Tabela $tabela - OK<br>";
                } else {
                    echo "⚠️ Tabela $tabela - NÃO ENCONTRADA<br>";
                    $avisos[] = "Tabela $tabela não encontrada";
                }
            }
            
        } catch (PDOException $e) {
            echo "❌ Erro na conexão com banco: " . $e->getMessage() . "<br>";
            $erros[] = 'Conexão com banco';
        }
    } else {
        echo "⚠️ Constantes do banco não definidas - Verificar arquivo .env<br>";
        $avisos[] = 'Constantes do banco não definidas';
    }
}

// 3. Verificar extensões PHP necessárias
echo "<h3>🐘 Verificando extensões PHP...</h3>";

$extensoes_necessarias = ['mysqli', 'json', 'mbstring', 'pdo', 'pdo_mysql'];
foreach ($extensoes_necessarias as $extensao) {
    if (extension_loaded($extensao)) {
        echo "✅ Extensão $extensao - OK<br>";
        $sucessos[] = "Extensão $extensao";
    } else {
        echo "❌ Extensão $extensao - FALTANDO<br>";
        $erros[] = "Extensão $extensao";
    }
}

// 4. Verificar versão do PHP
echo "<h3>📊 Verificando versão do PHP...</h3>";
$versao_php = PHP_VERSION;
echo "Versão atual: $versao_php<br>";

if (version_compare($versao_php, '7.4.0', '>=')) {
    echo "✅ Versão do PHP compatível<br>";
    $sucessos[] = 'Versão do PHP';
} else {
    echo "❌ Versão do PHP muito antiga (mínimo 7.4)<br>";
    $erros[] = 'Versão do PHP';
}

// 5. Verificar permissões de pastas
echo "<h3>🔐 Verificando permissões...</h3>";

$pastas_verificar = ['logs', 'public'];
foreach ($pastas_verificar as $pasta) {
    if (is_dir($pasta) && is_writable($pasta)) {
        echo "✅ Pasta $pasta - Gravável<br>";
        $sucessos[] = "Permissão $pasta";
    } else {
        echo "⚠️ Pasta $pasta - Não gravável<br>";
        $avisos[] = "Permissão $pasta";
    }
}

// 6. Verificar arquivos de debug
echo "<h3>🧹 Verificando arquivos de debug...</h3>";

$arquivos_debug = [
    'public/debug_agendamento.txt',
    'public/debug_agendamentos_dia.txt',
    'public/debug_pets_insert.txt',
    'public/debug-test.php',
    'public/debug-endpoint.php'
];

$debug_encontrados = false;
foreach ($arquivos_debug as $arquivo) {
    if (file_exists($arquivo)) {
        echo "⚠️ Arquivo de debug encontrado: $arquivo<br>";
        $debug_encontrados = true;
    }
}

if (!$debug_encontrados) {
    echo "✅ Nenhum arquivo de debug encontrado - OK<br>";
    $sucessos[] = 'Limpeza de debug';
} else {
    $avisos[] = 'Arquivos de debug encontrados';
}

// 7. Verificar credenciais expostas
echo "<h3>🔒 Verificando segurança...</h3>";

$arquivos_verificar_credenciais = ['src/Config.php', 'env.example'];
foreach ($arquivos_verificar_credenciais as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        if (strpos($conteudo, '!BdoB.1179!') !== false || 
            strpos($conteudo, 'bichosdobairro5') !== false) {
            echo "⚠️ Credenciais reais encontradas em $arquivo<br>";
            $avisos[] = "Credenciais em $arquivo";
        } else {
            echo "✅ $arquivo - Sem credenciais expostas<br>";
            $sucessos[] = "Segurança $arquivo";
        }
    }
}

// 8. Resumo final
echo "<h3>📋 RESUMO FINAL</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";

echo "<h4>✅ Sucessos (" . count($sucessos) . "):</h4>";
foreach ($sucessos as $sucesso) {
    echo "• $sucesso<br>";
}

if (!empty($avisos)) {
    echo "<h4>⚠️ Avisos (" . count($avisos) . "):</h4>";
    foreach ($avisos as $aviso) {
        echo "• $aviso<br>";
    }
}

if (!empty($erros)) {
    echo "<h4>❌ Erros (" . count($erros) . "):</h4>";
    foreach ($erros as $erro) {
        echo "• $erro<br>";
    }
}

echo "</div>";

// 9. Recomendação final
echo "<h3>🎯 RECOMENDAÇÃO</h3>";

if (empty($erros)) {
    if (empty($avisos)) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "<strong>✅ SISTEMA PRONTO PARA HOSPEDAGEM!</strong><br>";
        echo "Todos os requisitos foram atendidos. Pode fazer o upload com segurança.";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
        echo "<strong>⚠️ SISTEMA QUASE PRONTO</strong><br>";
        echo "Existem alguns avisos, mas o sistema pode ser enviado para hospedagem.";
        echo "Recomenda-se resolver os avisos antes do upload.";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<strong>❌ SISTEMA NÃO PRONTO</strong><br>";
    echo "Existem erros que devem ser corrigidos antes do upload.";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>📝 Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Corrigir erros (se houver)</li>";
echo "<li>Resolver avisos (recomendado)</li>";
echo "<li>Fazer backup do projeto</li>";
echo "<li>Upload para hospedagem</li>";
echo "<li>Configurar banco de dados</li>";
echo "<li>Configurar arquivo .env</li>";
echo "<li>Testar funcionalidades</li>";
echo "</ol>";

echo "<p><em>Verificação concluída em: " . date('d/m/Y H:i:s') . "</em></p>";
?> 