<?php
/**
 * Verificação Automática de Bugs e Problemas no Sistema
 * Sistema Bichos do Bairro
 * 
 * Este script faz uma varredura completa do sistema para identificar:
 * - Erros de sintaxe PHP
 * - Problemas de segurança
 * - Bugs em formulários
 * - Problemas de validação
 * - Inconsistências no código
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificação Automática de Bugs - Sistema Bichos do Bairro</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .error { color: #dc2626; background: #fef2f2; padding: 10px; margin: 5px 0; border-left: 4px solid #dc2626; }
    .warning { color: #d97706; background: #fffbeb; padding: 10px; margin: 5px 0; border-left: 4px solid #d97706; }
    .success { color: #059669; background: #f0fdf4; padding: 10px; margin: 5px 0; border-left: 4px solid #059669; }
    .info { color: #2563eb; background: #eff6ff; padding: 10px; margin: 5px 0; border-left: 4px solid #2563eb; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; }
    pre { background: #f3f4f6; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>";

$erros = [];
$avisos = [];
$sucessos = [];

// 1. Verificar sintaxe PHP
echo "<div class='section'>";
echo "<h2>📝 Verificação de Sintaxe PHP</h2>";

$arquivos_php = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && !str_contains($file->getPathname(), 'vendor/')) {
        $arquivos_php[] = $file->getPathname();
    }
}

$erros_sintaxe = 0;
foreach ($arquivos_php as $arquivo) {
    $output = [];
    $return_var = 0;
    exec("php -l " . escapeshellarg($arquivo) . " 2>&1", $output, $return_var);
    
    if ($return_var !== 0) {
        $erros_sintaxe++;
        $erros[] = "Erro de sintaxe em: $arquivo - " . implode(' ', $output);
        echo "<div class='error'>❌ Erro de sintaxe em: $arquivo<br>" . implode(' ', $output) . "</div>";
    }
}

if ($erros_sintaxe === 0) {
    $sucessos[] = "Nenhum erro de sintaxe encontrado em " . count($arquivos_php) . " arquivos PHP";
    echo "<div class='success'>✅ Nenhum erro de sintaxe encontrado em " . count($arquivos_php) . " arquivos PHP</div>";
}
echo "</div>";

// 2. Verificar problemas de segurança
echo "<div class='section'>";
echo "<h2>🔒 Verificação de Segurança</h2>";

// Verificar uso de htmlspecialchars
$arquivos_sem_escape = [];
foreach ($arquivos_php as $arquivo) {
    if (str_contains($arquivo, 'public/') || str_contains($arquivo, 'src/')) {
        $conteudo = file_get_contents($arquivo);
        $linhas = explode("\n", $conteudo);
        
        foreach ($linhas as $num => $linha) {
            if (preg_match('/echo.*\$/', $linha) && !str_contains($linha, 'htmlspecialchars')) {
                $arquivos_sem_escape[] = "$arquivo:$num";
            }
        }
    }
}

if (!empty($arquivos_sem_escape)) {
    $avisos[] = "Possível XSS detectado - variáveis não escapadas";
    echo "<div class='warning'>⚠️ Possível XSS detectado em:</div>";
    foreach (array_slice($arquivos_sem_escape, 0, 10) as $arquivo) {
        echo "<div class='warning'>   - $arquivo</div>";
    }
} else {
    $sucessos[] = "Nenhum problema de XSS detectado";
    echo "<div class='success'>✅ Nenhum problema de XSS detectado</div>";
}

// Verificar uso de prepared statements
$arquivos_sql_injection = [];
foreach ($arquivos_php as $arquivo) {
    if (str_contains($arquivo, 'public/') || str_contains($arquivo, 'src/')) {
        $conteudo = file_get_contents($arquivo);
        if (preg_match('/query.*\$_[POST|GET|REQUEST]/', $conteudo)) {
            $arquivos_sql_injection[] = $arquivo;
        }
    }
}

if (!empty($arquivos_sql_injection)) {
    $erros[] = "Possível SQL Injection detectado";
    echo "<div class='error'>❌ Possível SQL Injection detectado em:</div>";
    foreach ($arquivos_sql_injection as $arquivo) {
        echo "<div class='error'>   - $arquivo</div>";
    }
} else {
    $sucessos[] = "Nenhum problema de SQL Injection detectado";
    echo "<div class='success'>✅ Nenhum problema de SQL Injection detectado</div>";
}
echo "</div>";

// 3. Verificar problemas em formulários
echo "<div class='section'>";
echo "<h2>📋 Verificação de Formulários</h2>";

$formularios_sem_csrf = [];
foreach ($arquivos_php as $arquivo) {
    if (str_contains($arquivo, 'public/') && str_contains($arquivo, '.php')) {
        $conteudo = file_get_contents($arquivo);
        if (str_contains($conteudo, '<form') && !str_contains($conteudo, 'csrf') && !str_contains($arquivo, 'login.php')) {
            $formularios_sem_csrf[] = $arquivo;
        }
    }
}

if (!empty($formularios_sem_csrf)) {
    $avisos[] = "Formulários sem proteção CSRF";
    echo "<div class='warning'>⚠️ Formulários sem proteção CSRF:</div>";
    foreach ($formularios_sem_csrf as $arquivo) {
        echo "<div class='warning'>   - $arquivo</div>";
    }
} else {
    $sucessos[] = "Todos os formulários têm proteção CSRF";
    echo "<div class='success'>✅ Todos os formulários têm proteção CSRF</div>";
}

// Verificar validação de entrada
$formularios_sem_validacao = [];
foreach ($arquivos_php as $arquivo) {
    if (str_contains($arquivo, 'public/') && str_contains($arquivo, '.php')) {
        $conteudo = file_get_contents($arquivo);
        if (str_contains($conteudo, '$_POST') && !str_contains($conteudo, 'empty(') && !str_contains($conteudo, 'isset(')) {
            $formularios_sem_validacao[] = $arquivo;
        }
    }
}

if (!empty($formularios_sem_validacao)) {
    $avisos[] = "Formulários sem validação adequada";
    echo "<div class='warning'>⚠️ Formulários sem validação adequada:</div>";
    foreach ($formularios_sem_validacao as $arquivo) {
        echo "<div class='warning'>   - $arquivo</div>";
    }
} else {
    $sucessos[] = "Todos os formulários têm validação adequada";
    echo "<div class='success'>✅ Todos os formulários têm validação adequada</div>";
}
echo "</div>";

// 4. Verificar problemas de configuração
echo "<div class='section'>";
echo "<h2>⚙️ Verificação de Configuração</h2>";

// Verificar arquivos de configuração
$arquivos_config = ['.env', 'env.example', 'config-producao.env'];
foreach ($arquivos_config as $arquivo) {
    if (file_exists($arquivo)) {
        $sucessos[] = "Arquivo de configuração encontrado: $arquivo";
        echo "<div class='success'>✅ Arquivo de configuração encontrado: $arquivo</div>";
    } else {
        $avisos[] = "Arquivo de configuração não encontrado: $arquivo";
        echo "<div class='warning'>⚠️ Arquivo de configuração não encontrado: $arquivo</div>";
    }
}

// Verificar dependências
if (file_exists('composer.json')) {
    $composer = json_decode(file_get_contents('composer.json'), true);
    if (isset($composer['require'])) {
        $sucessos[] = "Dependências do Composer configuradas";
        echo "<div class='success'>✅ Dependências do Composer configuradas</div>";
    }
}

// Verificar estrutura de diretórios
$diretorios_essenciais = ['src', 'public', 'sql', 'backups', 'logs'];
foreach ($diretorios_essenciais as $dir) {
    if (is_dir($dir)) {
        $sucessos[] = "Diretório essencial encontrado: $dir";
        echo "<div class='success'>✅ Diretório essencial encontrado: $dir</div>";
    } else {
        $avisos[] = "Diretório essencial não encontrado: $dir";
        echo "<div class='warning'>⚠️ Diretório essencial não encontrado: $dir</div>";
    }
}
echo "</div>";

// 5. Verificar problemas de banco de dados
echo "<div class='section'>";
echo "<h2>🗄️ Verificação de Banco de Dados</h2>";

// Verificar arquivos SQL
$arquivos_sql = glob('sql/*.sql');
if (!empty($arquivos_sql)) {
    $sucessos[] = "Arquivos SQL encontrados: " . count($arquivos_sql);
    echo "<div class='success'>✅ Arquivos SQL encontrados: " . count($arquivos_sql) . "</div>";
} else {
    $avisos[] = "Nenhum arquivo SQL encontrado";
    echo "<div class='warning'>⚠️ Nenhum arquivo SQL encontrado</div>";
}

// Verificar classes de modelo
$classes_modelo = ['Cliente.php', 'Pet.php', 'Agendamento.php', 'Auth.php'];
foreach ($classes_modelo as $classe) {
    if (file_exists("src/$classe")) {
        $sucessos[] = "Classe de modelo encontrada: $classe";
        echo "<div class='success'>✅ Classe de modelo encontrada: $classe</div>";
    } else {
        $avisos[] = "Classe de modelo não encontrada: $classe";
        echo "<div class='warning'>⚠️ Classe de modelo não encontrada: $classe</div>";
    }
}
echo "</div>";

// 6. Verificar problemas de performance
echo "<div class='section'>";
echo "<h2>⚡ Verificação de Performance</h2>";

// Verificar loops infinitos ou queries N+1
$arquivos_com_loops = [];
foreach ($arquivos_php as $arquivo) {
    if (str_contains($arquivo, 'public/') || str_contains($arquivo, 'src/')) {
        $conteudo = file_get_contents($arquivo);
        if (preg_match('/while.*true|for.*;;/', $conteudo)) {
            $arquivos_com_loops[] = $arquivo;
        }
    }
}

if (!empty($arquivos_com_loops)) {
    $avisos[] = "Possíveis loops infinitos detectados";
    echo "<div class='warning'>⚠️ Possíveis loops infinitos detectados em:</div>";
    foreach ($arquivos_com_loops as $arquivo) {
        echo "<div class='warning'>   - $arquivo</div>";
    }
} else {
    $sucessos[] = "Nenhum loop infinito detectado";
    echo "<div class='success'>✅ Nenhum loop infinito detectado</div>";
}
echo "</div>";

// 7. Resumo final
echo "<div class='section'>";
echo "<h2>📊 Resumo da Verificação</h2>";

echo "<div class='info'>";
echo "<h3>Estatísticas:</h3>";
echo "<ul>";
echo "<li>Arquivos PHP verificados: " . count($arquivos_php) . "</li>";
echo "<li>Erros encontrados: " . count($erros) . "</li>";
echo "<li>Avisos encontrados: " . count($avisos) . "</li>";
echo "<li>Verificações bem-sucedidas: " . count($sucessos) . "</li>";
echo "</ul>";
echo "</div>";

if (!empty($erros)) {
    echo "<div class='error'>";
    echo "<h3>❌ Erros Críticos:</h3>";
    echo "<ul>";
    foreach ($erros as $erro) {
        echo "<li>$erro</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($avisos)) {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Avisos:</h3>";
    echo "<ul>";
    foreach ($avisos as $aviso) {
        echo "<li>$aviso</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($sucessos)) {
    echo "<div class='success'>";
    echo "<h3>✅ Verificações Bem-sucedidas:</h3>";
    echo "<ul>";
    foreach ($sucessos as $sucesso) {
        echo "<li>$sucesso</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Recomendações
echo "<div class='info'>";
echo "<h3>💡 Recomendações:</h3>";
echo "<ul>";
if (!empty($erros)) {
    echo "<li>Corrija os erros críticos antes de prosseguir</li>";
}
if (!empty($avisos)) {
    echo "<li>Revise os avisos para melhorar a segurança e performance</li>";
}
echo "<li>Execute testes unitários para validar as correções</li>";
echo "<li>Faça backup antes de aplicar mudanças</li>";
echo "<li>Teste em ambiente de desenvolvimento antes de produção</li>";
echo "</ul>";
echo "</div>";

echo "</div>";

echo "<div class='section'>";
echo "<h2>🎯 Próximos Passos</h2>";
echo "<ol>";
echo "<li>Corrigir erros críticos identificados</li>";
echo "<li>Implementar melhorias de segurança sugeridas</li>";
echo "<li>Otimizar performance onde necessário</li>";
echo "<li>Executar testes de integração</li>";
echo "<li>Documentar mudanças realizadas</li>";
echo "</ol>";
echo "</div>";

echo "<p><strong>Verificação concluída em: " . date('d/m/Y H:i:s') . "</strong></p>";
?> 