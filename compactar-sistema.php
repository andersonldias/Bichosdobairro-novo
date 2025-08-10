<?php
/**
 * Compactar Sistema Completo
 * Sistema Bichos do Bairro
 */

echo "=== COMPACTAÇÃO DO SISTEMA COMPLETO ===\n\n";

// Verificar se ZipArchive está disponível
if (!class_exists('ZipArchive')) {
    echo "❌ ERRO: Extensão ZipArchive não está disponível\n";
    echo "Instale a extensão zip do PHP ou use um programa externo\n";
    exit(1);
}

// Nome do arquivo ZIP
$timestamp = date('Y-m-d_H-i-s');
$zipFile = "bichosdobairro_sistema_completo_{$timestamp}.zip";

echo "1. Criando arquivo ZIP: $zipFile\n";

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
    echo "❌ Erro ao criar arquivo ZIP\n";
    exit(1);
}

// Lista de arquivos e diretórios para incluir
$includes = [
    'src/',
    'public/',
    'sql/',
    'vendor/',
    'logs/',
    'backups/',
    'cache/',
    'config-producao.env',
    'env.production',
    'deploy-producao.php',
    'verificar-producao.php',
    'backup-banco-completo.php',
    'restaurar-banco.php',
    'INSTRUCOES_DEPLOY_PRODUCAO.md',
    'README.md',
    'composer.json',
    'composer.lock'
];

// Lista de arquivos e diretórios para excluir
$excludes = [
    '.git/',
    '.gitignore',
    'node_modules/',
    '*.log',
    '*.tmp',
    '*.cache',
    'Thumbs.db',
    '.DS_Store'
];

echo "2. Adicionando arquivos ao ZIP...\n";

$totalFiles = 0;
$totalSize = 0;

function addToZip($zip, $path, $basePath = '') {
    global $excludes, $totalFiles, $totalSize;
    
    // Verificar se deve ser excluído
    foreach ($excludes as $exclude) {
        if (fnmatch($exclude, basename($path))) {
            return;
        }
    }
    
    if (is_dir($path)) {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $fullPath = $path . '/' . $file;
                $zipPath = $basePath . basename($path) . '/' . $file;
                
                // Verificar se deve ser excluído
                $shouldExclude = false;
                foreach ($excludes as $exclude) {
                    if (fnmatch($exclude, $file)) {
                        $shouldExclude = true;
                        break;
                    }
                }
                
                if (!$shouldExclude) {
                    addToZip($zip, $fullPath, $basePath . basename($path) . '/');
                }
            }
        }
    } else {
        if (file_exists($path)) {
            $zipPath = $basePath . basename($path);
            $zip->addFile($path, $zipPath);
            $totalFiles++;
            $totalSize += filesize($path);
            
            if ($totalFiles % 10 == 0) {
                echo "   Arquivos adicionados: $totalFiles\n";
            }
        }
    }
}

// Adicionar arquivos
foreach ($includes as $include) {
    if (file_exists($include)) {
        echo "   Adicionando: $include\n";
        addToZip($zip, $include);
    } else {
        echo "   ⚠️  Não encontrado: $include\n";
    }
}

$zip->close();

echo "\n3. Compactação concluída!\n";
echo "✅ Arquivo ZIP: $zipFile\n";

$zipSize = filesize($zipFile);
$zipSizeMB = round($zipSize / 1024 / 1024, 2);
echo "✅ Tamanho: $zipSizeMB MB\n";
echo "✅ Arquivos incluídos: $totalFiles\n";

// Criar arquivo de informações
$infoFile = "info_sistema_{$timestamp}.txt";
$info = "=== INFORMAÇÕES DO SISTEMA ===\n\n";
$info .= "Data/Hora: " . date('d/m/Y H:i:s') . "\n";
$info .= "Arquivo ZIP: $zipFile\n";
$info .= "Tamanho: $zipSizeMB MB\n";
$info .= "Arquivos: $totalFiles\n\n";

$info .= "=== CONTEÚDO INCLUÍDO ===\n\n";
foreach ($includes as $include) {
    if (file_exists($include)) {
        $info .= "✅ $include\n";
    } else {
        $info .= "❌ $include (não encontrado)\n";
    }
}

$info .= "\n=== INSTRUÇÕES DE INSTALAÇÃO ===\n\n";
$info .= "1. Faça upload do arquivo ZIP para o servidor\n";
$info .= "2. Extraia o conteúdo na pasta do servidor\n";
$info .= "3. Configure o arquivo .env com as credenciais do banco\n";
$info .= "4. Execute o script de deploy: php deploy-producao.php\n";
$info .= "5. Restaure o banco de dados se necessário\n";
$info .= "6. Configure o DocumentRoot para a pasta public/\n";
$info .= "7. Teste o sistema\n\n";

$info .= "=== ARQUIVOS IMPORTANTES ===\n\n";
$info .= "- config-producao.env: Configurações de produção\n";
$info .= "- INSTRUCOES_DEPLOY_PRODUCAO.md: Documentação completa\n";
$info .= "- deploy-producao.php: Script de deploy automatizado\n";
$info .= "- verificar-producao.php: Verificação do sistema\n";
$info .= "- backup-banco-completo.php: Backup do banco\n";
$info .= "- restaurar-banco.php: Restauração do banco\n\n";

$info .= "=== CREDENCIAIS PADRÃO ===\n\n";
$info .= "Email: admin@bichosdobairro.com\n";
$info .= "Senha: admin123\n\n";

$info .= "=== SUPORTE ===\n\n";
$info .= "Logs: logs/app.log\n";
$info .= "Documentação: INSTRUCOES_DEPLOY_PRODUCAO.md\n";
$info .= "Configuração: config-producao.env\n";

file_put_contents($infoFile, $info);
echo "✅ Arquivo de informações: $infoFile\n";

echo "\n=== RESUMO DA COMPACTAÇÃO ===\n";
echo "✅ Sistema compactado: $zipFile\n";
echo "✅ Tamanho: $zipSizeMB MB\n";
echo "✅ Arquivos: $totalFiles\n";
echo "✅ Informações: $infoFile\n";
echo "✅ Status: SISTEMA PRONTO PARA ENVIO!\n\n";

echo "=== PRÓXIMOS PASSOS ===\n";
echo "1. Faça download do arquivo ZIP\n";
echo "2. Envie para o novo servidor\n";
echo "3. Extraia o conteúdo\n";
echo "4. Configure o sistema\n";
echo "5. Restaure o banco se necessário\n\n";

echo "🎉 SISTEMA COMPLETO PRONTO PARA TRANSFERÊNCIA! 🎉\n";
?> 