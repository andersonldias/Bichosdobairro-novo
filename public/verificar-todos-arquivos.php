<?php
/**
 * Script para verificar todos os arquivos PHP em busca de erros de sintaxe
 * Sistema Bichos do Bairro
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificação Completa de Arquivos PHP</h1>";

// Função para verificar sintaxe de um arquivo
function verificarSintaxe($arquivo) {
    $output = [];
    $returnCode = 0;
    
    exec("php -l \"$arquivo\" 2>&1", $output, $returnCode);
    
    return [
        'arquivo' => $arquivo,
        'valido' => $returnCode === 0,
        'erro' => implode("\n", $output),
        'return_code' => $returnCode
    ];
}

// Função para listar todos os arquivos PHP
function listarArquivosPHP($diretorio) {
    $arquivos = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($diretorio, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $arquivos[] = $file->getPathname();
        }
    }
    
    return $arquivos;
}

// Verificar arquivos PHP
$diretorios = ['../src', './', '../scripts', '../backups'];

$totalArquivos = 0;
$arquivosComErro = 0;
$erros = [];

foreach ($diretorios as $dir) {
    if (is_dir($dir)) {
        echo "<h2>Verificando diretório: $dir</h2>";
        
        $arquivos = listarArquivosPHP($dir);
        $totalArquivos += count($arquivos);
        
        foreach ($arquivos as $arquivo) {
            $resultado = verificarSintaxe($arquivo);
            
            if (!$resultado['valido']) {
                $arquivosComErro++;
                $erros[] = $resultado;
                echo "<p style='color: red;'>❌ " . basename($arquivo) . " - ERRO</p>";
                echo "<pre style='background: #f0f0f0; padding: 10px; margin: 5px 0;'>" . htmlspecialchars($resultado['erro']) . "</pre>";
            } else {
                echo "<p style='color: green;'>✅ " . basename($arquivo) . "</p>";
            }
        }
    }
}

echo "<h2>📊 Resumo</h2>";
echo "<p>Total de arquivos verificados: $totalArquivos</p>";
echo "<p>Arquivos com erro: $arquivosComErro</p>";
echo "<p>Arquivos válidos: " . ($totalArquivos - $arquivosComErro) . "</p>";

if ($arquivosComErro > 0) {
    echo "<h2>🚨 Erros Encontrados</h2>";
    echo "<ol>";
    foreach ($erros as $erro) {
        echo "<li><strong>" . basename($erro['arquivo']) . "</strong><br>";
        echo "<code>" . htmlspecialchars($erro['erro']) . "</code></li>";
    }
    echo "</ol>";
    
    echo "<h2>🔧 Ações Recomendadas</h2>";
    echo "<ol>";
    echo "<li>Corrija os erros de sintaxe listados acima</li>";
    echo "<li>Verifique se há chaves não fechadas</li>";
    echo "<li>Verifique se há parênteses não fechados</li>";
    echo "<li>Verifique se há aspas não fechadas</li>";
    echo "<li>Verifique se há ponto e vírgula faltando</li>";
    echo "</ol>";
} else {
    echo "<h2>🎉 Todos os arquivos estão com sintaxe válida!</h2>";
}

echo "<h2>🔍 Verificação de Problemas Comuns</h2>";

// Verificar problemas de output antes de headers
echo "<h3>Verificando problemas de output antes de headers...</h3>";

$problemasOutput = [];
foreach ($diretorios as $dir) {
    if (is_dir($dir)) {
        $arquivos = listarArquivosPHP($dir);
        foreach ($arquivos as $arquivo) {
            $conteudo = file_get_contents($arquivo);
            
            // Verificar se há espaços ou quebras de linha antes de <?php
            if (preg_match('/^\s+/', $conteudo)) {
                $problemasOutput[] = [
                    'arquivo' => $arquivo,
                    'problema' => 'Espaços ou quebras de linha antes de <?php',
                    'linha' => 1
                ];
            }
            
            // Verificar se há output antes de headers
            $linhas = explode("\n", $conteudo);
            foreach ($linhas as $num => $linha) {
                $num++; // Linhas começam em 1
                
                // Verificar echo, print, var_dump antes de header()
                if (preg_match('/^\s*(echo|print|var_dump|print_r)/', $linha)) {
                    // Verificar se há header() depois
                    $restoArquivo = implode("\n", array_slice($linhas, $num));
                    if (preg_match('/header\s*\(/', $restoArquivo)) {
                        $problemasOutput[] = [
                            'arquivo' => $arquivo,
                            'problema' => 'Output antes de header()',
                            'linha' => $num,
                            'codigo' => trim($linha)
                        ];
                    }
                }
            }
        }
    }
}

if (!empty($problemasOutput)) {
    echo "<h3>⚠️ Problemas de Output Encontrados</h3>";
    echo "<ul>";
    foreach ($problemasOutput as $problema) {
        echo "<li><strong>" . basename($problema['arquivo']) . "</strong> (linha " . $problema['linha'] . "): ";
        echo $problema['problema'];
        if (isset($problema['codigo'])) {
            echo " - <code>" . htmlspecialchars($problema['codigo']) . "</code>";
        }
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>✅ Nenhum problema de output encontrado</p>";
}

echo "<p><a href='index.php'>← Voltar ao sistema</a></p>";
?>
