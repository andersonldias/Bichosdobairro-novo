<?php
/**
 * Verificação Completa de Bugs e Erros
 * Sistema Bichos do Bairro
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificação de Bugs - Bichos do Bairro</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-50 p-4'>
    <div class='max-w-6xl mx-auto'>
        <div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
            <h1 class='text-2xl font-bold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-bug text-red-500 mr-3'></i>
                Verificação Completa de Bugs e Erros
            </h1>
            <p class='text-gray-600'>Análise completa do código em busca de bugs, erros de sintaxe e problemas.</p>
        </div>";

// Função para exibir resultado
function exibirResultado($titulo, $status, $mensagem, $detalhes = null) {
    $statusClass = $status ? 'text-green-600' : 'text-red-600';
    $statusIcon = $status ? 'fa-check-circle' : 'fa-times-circle';
    $bgClass = $status ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    
    echo "<div class='bg-white rounded-lg shadow p-4 mb-4 border-l-4 {$bgClass}'>
        <div class='flex items-center justify-between mb-2'>
            <h3 class='font-semibold text-gray-800'>$titulo</h3>
            <i class='fas $statusIcon $statusClass text-xl'></i>
        </div>
        <p class='text-gray-700 mb-2'>$mensagem</p>";
    
    if ($detalhes) {
        echo "<details class='mt-2'>
            <summary class='cursor-pointer text-sm text-blue-600 hover:text-blue-800'>Ver detalhes</summary>
            <div class='mt-2 p-3 bg-gray-100 rounded text-sm font-mono'>$detalhes</div>
        </details>";
    }
    
    echo "</div>";
}

// Função para verificar sintaxe de arquivo PHP
function verificarSintaxe($arquivo) {
    $output = [];
    $returnCode = 0;
    exec("php -l \"$arquivo\" 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        return ['status' => true, 'mensagem' => 'Sintaxe OK'];
    } else {
        return ['status' => false, 'mensagem' => implode("\n", $output)];
    }
}

// 1. Verificar sintaxe dos arquivos principais
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>1. Verificação de Sintaxe PHP</h2>";

$arquivosPrincipais = [
    '../src/Config.php' => 'Classe de Configuração',
    '../src/Utils.php' => 'Classe de Utilitários',
    '../src/db.php' => 'Conexão com Banco',
    '../src/init.php' => 'Inicialização do Sistema',
    '../src/autoload.php' => 'Sistema de Autoload',
    'layout.php' => 'Layout Principal',
    'index.php' => 'Página Inicial',
    'dashboard.php' => 'Dashboard',
    'clientes.php' => 'Gestão de Clientes',
    'pets.php' => 'Gestão de Pets',
    'agendamentos.php' => 'Agendamentos'
];

$errosSintaxe = [];
$sucessosSintaxe = [];

foreach ($arquivosPrincipais as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        $resultado = verificarSintaxe($arquivo);
        if ($resultado['status']) {
            $sucessosSintaxe[] = "$descricao ($arquivo)";
        } else {
            $errosSintaxe[] = "$descricao ($arquivo): " . $resultado['mensagem'];
        }
    } else {
        $errosSintaxe[] = "$descricao ($arquivo): Arquivo não encontrado";
    }
}

if (empty($errosSintaxe)) {
    exibirResultado('Sintaxe PHP', true, count($sucessosSintaxe) . ' arquivos verificados sem erros de sintaxe', null);
} else {
    exibirResultado('Sintaxe PHP', false, count($errosSintaxe) . ' erro(s) de sintaxe encontrado(s)', implode("\n", $errosSintaxe));
}

echo "</div>";

// 2. Verificar problemas de sessão
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>2. Verificação de Sessões</h2>";

$problemasSessao = [];
$arquivosComSessao = [
    'dashboard.php' => 'Verifica $_SESSION sem session_start()',
    'caixa.php' => 'Usa $_SESSION para caixa',
    'layout.php' => 'Exibe dados da sessão'
];

foreach ($arquivosComSessao as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        if (strpos($conteudo, '$_SESSION') !== false && strpos($conteudo, 'session_start()') === false && strpos($conteudo, 'require_once') === false) {
            $problemasSessao[] = "$arquivo: $descricao";
        }
    }
}

if (empty($problemasSessao)) {
    exibirResultado('Sessões', true, 'Sessões configuradas corretamente', null);
} else {
    exibirResultado('Sessões', false, count($problemasSessao) . ' problema(s) com sessões encontrado(s)', implode("\n", $problemasSessao));
}

echo "</div>";

// 3. Verificar variáveis não definidas
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>3. Verificação de Variáveis</h2>";

$problemasVariaveis = [];
$arquivosPHP = glob('*.php');
$arquivosSrc = glob('../src/*.php');

foreach (array_merge($arquivosPHP, $arquivosSrc) as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Verificar variáveis $_POST, $_GET, $_REQUEST sem isset
        if (preg_match_all('/\$_(POST|GET|REQUEST)\s*\[\s*[\'"][^\'"]*[\'"]\s*\]/', $conteudo, $matches)) {
            foreach ($matches[0] as $match) {
                if (strpos($conteudo, 'isset(' . $match . ')') === false) {
                    $problemasVariaveis[] = "$arquivo: Variável $match sem verificação isset()";
                }
            }
        }
    }
}

if (empty($problemasVariaveis)) {
    exibirResultado('Variáveis', true, 'Variáveis verificadas corretamente', null);
} else {
    exibirResultado('Variáveis', false, count($problemasVariaveis) . ' problema(s) com variáveis encontrado(s)', implode("\n", array_slice($problemasVariaveis, 0, 10)));
}

echo "</div>";

// 4. Verificar problemas de segurança
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>4. Verificação de Segurança</h2>";

$problemasSeguranca = [];
$arquivosPHP = glob('*.php');

foreach ($arquivosPHP as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Verificar saída direta de variáveis sem htmlspecialchars
        if (preg_match_all('/echo\s+\$[a-zA-Z_][a-zA-Z0-9_]*/', $conteudo, $matches)) {
            foreach ($matches[0] as $match) {
                if (strpos($conteudo, 'htmlspecialchars') === false) {
                    $problemasSeguranca[] = "$arquivo: Saída direta sem sanitização: $match";
                }
            }
        }
        
        // Verificar SQL injection potencial
        if (strpos($conteudo, '$_POST') !== false && strpos($conteudo, 'query(') !== false) {
            $problemasSeguranca[] = "$arquivo: Possível SQL injection - verificar queries";
        }
    }
}

if (empty($problemasSeguranca)) {
    exibirResultado('Segurança', true, 'Problemas de segurança não encontrados', null);
} else {
    exibirResultado('Segurança', false, count($problemasSeguranca) . ' problema(s) de segurança encontrado(s)', implode("\n", array_slice($problemasSeguranca, 0, 10)));
}

echo "</div>";

// 5. Verificar dependências
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>5. Verificação de Dependências</h2>";

$problemasDependencias = [];
$arquivosPHP = glob('*.php');

foreach ($arquivosPHP as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Verificar require_once e include
        if (preg_match_all('/(require_once|include_once|require|include)\s+[\'"]([^\'"]+)[\'"]/', $conteudo, $matches)) {
            foreach ($matches[2] as $match) {
                if (!file_exists($match) && !file_exists('../src/' . $match)) {
                    $problemasDependencias[] = "$arquivo: Arquivo não encontrado: $match";
                }
            }
        }
    }
}

if (empty($problemasDependencias)) {
    exibirResultado('Dependências', true, 'Todas as dependências encontradas', null);
} else {
    exibirResultado('Dependências', false, count($problemasDependencias) . ' problema(s) com dependências encontrado(s)', implode("\n", $problemasDependencias));
}

echo "</div>";

// 6. Verificar problemas de performance
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>6. Verificação de Performance</h2>";

$problemasPerformance = [];
$arquivosPHP = glob('*.php');

foreach ($arquivosPHP as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Verificar loops sem limite
        if (preg_match('/while\s*\([^)]*\)\s*\{/', $conteudo)) {
            $problemasPerformance[] = "$arquivo: Loop while sem limite definido";
        }
        
        // Verificar queries em loops
        if (strpos($conteudo, 'foreach') !== false && strpos($conteudo, 'query(') !== false) {
            $problemasPerformance[] = "$arquivo: Possível N+1 query problem";
        }
    }
}

if (empty($problemasPerformance)) {
    exibirResultado('Performance', true, 'Problemas de performance não encontrados', null);
} else {
    exibirResultado('Performance', false, count($problemasPerformance) . ' problema(s) de performance encontrado(s)', implode("\n", $problemasPerformance));
}

echo "</div>";

// 7. Verificar problemas de compatibilidade
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>7. Verificação de Compatibilidade</h2>";

$problemasCompatibilidade = [];
$arquivosPHP = glob('*.php');

foreach ($arquivosPHP as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Verificar funções deprecated
        $funcoesDeprecated = ['mysql_', 'ereg_', 'split'];
        foreach ($funcoesDeprecated as $funcao) {
            if (strpos($conteudo, $funcao) !== false) {
                $problemasCompatibilidade[] = "$arquivo: Função deprecated encontrada: $funcao";
            }
        }
        
        // Verificar sintaxe PHP 8+
        if (strpos($conteudo, '??') !== false && strpos($conteudo, '?->') !== false) {
            $problemasCompatibilidade[] = "$arquivo: Sintaxe PHP 8+ detectada";
        }
    }
}

if (empty($problemasCompatibilidade)) {
    exibirResultado('Compatibilidade', true, 'Código compatível com PHP 7.4+', null);
} else {
    exibirResultado('Compatibilidade', false, count($problemasCompatibilidade) . ' problema(s) de compatibilidade encontrado(s)', implode("\n", $problemasCompatibilidade));
}

echo "</div>";

// 8. Resumo final
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>8. Resumo Final</h2>";

$totalProblemas = count($errosSintaxe) + count($problemasSessao) + count($problemasVariaveis) + 
                  count($problemasSeguranca) + count($problemasDependencias) + 
                  count($problemasPerformance) + count($problemasCompatibilidade);

if ($totalProblemas === 0) {
    echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>
            <h3 class='text-lg font-semibold text-green-800'>Código Limpo!</h3>
        </div>
        <p class='text-green-700 mt-2'>Nenhum bug ou erro encontrado. O código está em excelente estado.</p>
    </div>";
} else {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-exclamation-triangle text-red-600 text-xl mr-3'></i>
            <h3 class='text-lg font-semibold text-red-800'>Problemas Encontrados</h3>
        </div>
        <p class='text-red-700 mt-2'>$totalProblemas problema(s) encontrado(s) no código.</p>
        <div class='mt-4 space-y-2'>
            <p class='text-red-700'><strong>Resumo dos problemas:</strong></p>
            <ul class='text-red-700 list-disc list-inside ml-4'>";
    
    if (!empty($errosSintaxe)) echo "<li>" . count($errosSintaxe) . " erro(s) de sintaxe</li>";
    if (!empty($problemasSessao)) echo "<li>" . count($problemasSessao) . " problema(s) com sessões</li>";
    if (!empty($problemasVariaveis)) echo "<li>" . count($problemasVariaveis) . " problema(s) com variáveis</li>";
    if (!empty($problemasSeguranca)) echo "<li>" . count($problemasSeguranca) . " problema(s) de segurança</li>";
    if (!empty($problemasDependencias)) echo "<li>" . count($problemasDependencias) . " problema(s) com dependências</li>";
    if (!empty($problemasPerformance)) echo "<li>" . count($problemasPerformance) . " problema(s) de performance</li>";
    if (!empty($problemasCompatibilidade)) echo "<li>" . count($problemasCompatibilidade) . " problema(s) de compatibilidade</li>";
    
    echo "</ul>
        </div>
    </div>";
}

echo "</div>";

echo "<div class='bg-gray-100 rounded-lg p-4 text-center'>
    <p class='text-gray-600 mb-4'>Verificação concluída em " . date('d/m/Y H:i:s') . "</p>
    <div class='space-x-4'>
        <a href='dashboard.php' class='inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>
            <i class='fas fa-home mr-2'></i>Dashboard
        </a>
        <a href='verificar-banco-simples.php' class='inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600'>
            <i class='fas fa-database mr-2'></i>Verificar Banco
        </a>
    </div>
</div>";

echo "</div>
</body>
</html>";
?> 