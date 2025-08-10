<?php
/**
 * Script para Compactar Sistema para FTP
 * Sistema Bichos do Bairro
 */

echo "=== COMPACTAÇÃO DO SISTEMA PARA FTP ===\n\n";

// Verificar se ZipArchive está disponível
if (!class_exists('ZipArchive')) {
    echo "❌ ZipArchive não está disponível no PHP\n";
    echo "Vou criar uma lista de arquivos para envio manual\n";
    
    // Criar lista de arquivos
    $files = [];
    $dirs = ['public', 'src', 'vendor', 'sql'];
    
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $files[] = $file->getPathname();
                }
            }
        }
    }
    
    // Adicionar arquivos da raiz
    $rootFiles = [
        'composer.json',
        'composer.lock',
        'env.example',
        'config-producao.env',
        'nginx.conf',
        '.htaccess',
        'README.md'
    ];
    
    foreach ($rootFiles as $file) {
        if (file_exists($file)) {
            $files[] = $file;
        }
    }
    
    // Salvar lista
    file_put_contents('arquivos_para_ftp.txt', implode("\n", $files));
    echo "✅ Lista de arquivos salva em: arquivos_para_ftp.txt\n";
    echo "Total de arquivos: " . count($files) . "\n";
    
    exit(0);
}

// Criar arquivo ZIP
$zipFile = 'bichosdobairro_' . date('Y-m-d_H-i-s') . '.zip';
echo "1. Criando arquivo ZIP: $zipFile\n";

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
    echo "❌ Erro ao criar arquivo ZIP\n";
    exit(1);
}

// Função para adicionar arquivos
function addToZip($zip, $path, $basePath = '') {
    if (is_file($path)) {
        $relativePath = $basePath . basename($path);
        $zip->addFile($path, $relativePath);
        echo "   ✅ Adicionado: $relativePath\n";
    } elseif (is_dir($path)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = $basePath . $file->getPathname();
                $zip->addFile($file->getPathname(), $relativePath);
            }
        }
    }
}

// Adicionar diretórios principais
$dirs = [
    'public' => 'public',
    'src' => 'src', 
    'vendor' => 'vendor',
    'sql' => 'sql'
];

foreach ($dirs as $dir => $zipPath) {
    if (is_dir($dir)) {
        echo "2. Adicionando diretório: $dir\n";
        addToZip($zip, $dir, $zipPath . '/');
    }
}

// Adicionar arquivos da raiz
echo "3. Adicionando arquivos da raiz\n";
$rootFiles = [
    'composer.json',
    'composer.lock',
    'env.example',
    'config-producao.env',
    'nginx.conf',
    '.htaccess',
    'README.md',
    'start-dev.bat',
    'start-dev.sh'
];

foreach ($rootFiles as $file) {
    if (file_exists($file)) {
        $zip->addFile($file, $file);
        echo "   ✅ Adicionado: $file\n";
    }
}

$zip->close();

echo "\n✅ Arquivo ZIP criado com sucesso!\n";
echo "📁 Arquivo: $zipFile\n";
echo "📊 Tamanho: " . number_format(filesize($zipFile) / 1024 / 1024, 2) . " MB\n";

// Criar instruções de FTP
echo "\n4. Criando instruções de FTP...\n";

$ftpInstructions = "# INSTRUÇÕES DE FTP - BICHOS DO BAIRRO\n\n";
$ftpInstructions .= "## 📁 ARQUIVO PARA ENVIAR\n";
$ftpInstructions .= "Arquivo: $zipFile\n";
$ftpInstructions .= "Tamanho: " . number_format(filesize($zipFile) / 1024 / 1024, 2) . " MB\n\n";

$ftpInstructions .= "## 🚀 PASSO A PASSO FTP\n\n";
$ftpInstructions .= "### 1. Conectar via FTP\n";
$ftpInstructions .= "- Host: (dados da hospedagem)\n";
$ftpInstructions .= "- Usuário: (dados da hospedagem)\n";
$ftpInstructions .= "- Senha: (dados da hospedagem)\n";
$ftpInstructions .= "- Porta: 21 (padrão)\n\n";

$ftpInstructions .= "### 2. Navegar para pasta do site\n";
$ftpInstructions .= "- Geralmente: /public_html/ ou /www/\n";
$ftpInstructions .= "- Ou pasta específica do domínio\n\n";

$ftpInstructions .= "### 3. Fazer backup do site atual (se existir)\n";
$ftpInstructions .= "- Renomear pasta atual para: site_old\n";
$ftpInstructions .= "- Ou fazer backup via painel da hospedagem\n\n";

$ftpInstructions .= "### 4. Enviar arquivo ZIP\n";
$ftpInstructions .= "- Fazer upload do arquivo: $zipFile\n";
$ftpInstructions .= "- Aguardar conclusão do upload\n\n";

$ftpInstructions .= "### 5. Extrair arquivo ZIP\n";
$ftpInstructions .= "- Via painel da hospedagem (File Manager)\n";
$ftpInstructions .= "- Ou via SSH: unzip $zipFile\n";
$ftpInstructions .= "- Mover arquivos para pasta correta\n\n";

$ftpInstructions .= "### 6. Configurar arquivo .env\n";
$ftpInstructions .= "- Copiar config-producao.env para .env\n";
$ftpInstructions .= "- Ajustar credenciais do banco\n";
$ftpInstructions .= "- Configurar URL do site\n\n";

$ftpInstructions .= "### 7. Verificar permissões\n";
$ftpInstructions .= "- Pasta logs/: 755\n";
$ftpInstructions .= "- Arquivo .env: 644\n";
$ftpInstructions .= "- Outros arquivos: 644\n\n";

$ftpInstructions .= "### 8. Testar sistema\n";
$ftpInstructions .= "- Acessar: https://seudominio.com\n";
$ftpInstructions .= "- Login: admin / admin123\n";
$ftpInstructions .= "- Verificar funcionalidades\n\n";

$ftpInstructions .= "## ⚠️ IMPORTANTE\n\n";
$ftpInstructions .= "- Faça backup antes de substituir\n";
$ftpInstructions .= "- Verifique se o banco foi importado\n";
$ftpInstructions .= "- Configure o arquivo .env corretamente\n";
$ftpInstructions .= "- Teste todas as funcionalidades\n\n";

$ftpInstructions .= "## 📞 EM CASO DE PROBLEMAS\n\n";
$ftpInstructions .= "1. Verifique logs de erro\n";
$ftpInstructions .= "2. Confirme credenciais do banco\n";
$ftpInstructions .= "3. Verifique permissões de arquivos\n";
$ftpInstructions .= "4. Teste conexão com banco\n";

file_put_contents('INSTRUCOES_FTP.md', $ftpInstructions);

echo "✅ Instruções salvas em: INSTRUCOES_FTP.md\n";

echo "\n🎉 SISTEMA PRONTO PARA FTP!\n";
echo "📁 Arquivo: $zipFile\n";
echo "📖 Instruções: INSTRUCOES_FTP.md\n";
echo "\n🚀 Agora envie o arquivo ZIP via FTP!\n"; 