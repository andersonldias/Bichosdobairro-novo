<?php
/**
 * Script para corrigir problemas no .htaccess
 * Sistema Bichos do Bairro
 */

echo "<h1>Correção do Arquivo .htaccess</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// Verificar se o arquivo .htaccess existe
$htaccess_path = __DIR__ . '/../.htaccess';

echo "<h2>1. Verificação do Arquivo .htaccess</h2>";

if (file_exists($htaccess_path)) {
    echo "<p style='color: green;'>✅ Arquivo .htaccess existe</p>";
    
    // Ler conteúdo atual
    $conteudo_atual = file_get_contents($htaccess_path);
    echo "<details><summary>Conteúdo atual do .htaccess</summary><pre>";
    echo htmlspecialchars($conteudo_atual);
    echo "</pre></details>";
} else {
    echo "<p style='color: red;'>❌ Arquivo .htaccess não existe - será criado</p>";
    $conteudo_atual = '';
}

// Conteúdo recomendado para o .htaccess
$conteudo_recomendado = '# ========================================
# CONFIGURAÇÕES .HTACCESS - BICHOS DO BAIRRO
# ========================================

# Habilitar rewrite engine
RewriteEngine On

# Configurações de segurança
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

# Proteger diretórios sensíveis
<DirectoryMatch "^/.*/(src|vendor|logs|sql)/">
    Order allow,deny
    Deny from all
</DirectoryMatch>

# Configurações de cache para arquivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

# Compressão GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Configurações de PHP
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value max_input_vars 3000
</IfModule>

# Headers de segurança
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Redirecionar www para non-www (opcional)
# RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
# RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

# Redirecionar HTTP para HTTPS (se disponível)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Configurações de erro personalizadas
ErrorDocument 404 /public/404.php
ErrorDocument 500 /public/500.php

# Regras de rewrite para URLs amigáveis (se necessário)
# RewriteCond %{REQUEST_FILENAME} !-f
# RewriteCond %{REQUEST_FILENAME} !-d
# RewriteRule ^(.*)$ /public/index.php [QSA,L]

# Configurações específicas para hospedagem compartilhada
<IfModule mod_rewrite.c>
    # Permitir acesso ao diretório public
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /public/$1 [L]
</IfModule>

# Configurações de sessão
php_value session.cookie_httponly 1
php_value session.use_strict_mode 1
php_value session.cookie_secure 0
php_value session.cookie_samesite "Lax"

# Configurações de timezone
php_value date.timezone "America/Sao_Paulo"

# Configurações de charset
AddDefaultCharset UTF-8

# Configurações de cache para PHP
<FilesMatch "\.(php)$">
    <IfModule mod_expires.c>
        ExpiresActive Off
    </IfModule>
    <IfModule mod_headers.c>
        Header set Cache-Control "no-cache, no-store, must-revalidate"
        Header set Pragma "no-cache"
        Header set Expires 0
    </IfModule>
</FilesMatch>';

echo "<h2>2. Conteúdo Recomendado</h2>";
echo "<details><summary>Conteúdo recomendado para o .htaccess</summary><pre>";
echo htmlspecialchars($conteudo_recomendado);
echo "</pre></details>";

// Verificar se precisa atualizar
if ($conteudo_atual === $conteudo_recomendado) {
    echo "<p style='color: green;'>✅ O arquivo .htaccess já está com o conteúdo correto</p>";
} else {
    echo "<h2>3. Atualização do .htaccess</h2>";
    
    // Fazer backup do arquivo atual
    if (!empty($conteudo_atual)) {
        $backup_path = $htaccess_path . '.backup.' . date('Y-m-d-H-i-s');
        if (file_put_contents($backup_path, $conteudo_atual)) {
            echo "<p style='color: green;'>✅ Backup criado: " . basename($backup_path) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar backup</p>";
        }
    }
    
    // Atualizar o arquivo
    if (file_put_contents($htaccess_path, $conteudo_recomendado)) {
        echo "<p style='color: green;'>✅ Arquivo .htaccess atualizado com sucesso</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao atualizar o arquivo .htaccess</p>";
        echo "<p>Verifique as permissões do arquivo</p>";
    }
}

echo "<h2>4. Verificação de Permissões</h2>";
if (file_exists($htaccess_path)) {
    $permissoes = substr(sprintf('%o', fileperms($htaccess_path)), -4);
    echo "<p><strong>Permissões do .htaccess:</strong> $permissoes</p>";
    
    if ($permissoes === '0644' || $permissoes === '0664') {
        echo "<p style='color: green;'>✅ Permissões corretas</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Permissões podem estar incorretas</p>";
        echo "<p>Recomendado: 644 (rw-r--r--)</p>";
    }
}

echo "<h2>5. Teste de Sintaxe</h2>";
echo "<p>Para testar se o .htaccess está funcionando:</p>";
echo "<ol>";
echo "<li>Acesse: <code>https://meuapp.bichosdobairro.com.br/public/teste-erro-500.php</code></li>";
echo "<li>Se funcionar, o .htaccess está correto</li>";
echo "<li>Se der erro, pode haver problema de sintaxe</li>";
echo "</ol>";

echo "<h2>6. Próximos Passos</h2>";
echo "<ul>";
echo "<li>Teste o site principal novamente</li>";
echo "<li>Se ainda der erro 500, verifique os logs do servidor</li>";
echo "<li>Execute o diagnóstico completo: <code>public/diagnostico-erro-500.php</code></li>";
echo "<li>Verifique se o servidor suporta as diretivas do .htaccess</li>";
echo "</ul>";

echo "<hr>";
echo "<p><em>Correção concluída em " . date('d/m/Y H:i:s') . "</em></p>";
?> 