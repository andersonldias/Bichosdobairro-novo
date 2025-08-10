# 🔧 Configuração do Ambiente - Sistema Bichos do Bairro

## 📋 Índice

1. [Requisitos do Sistema](#requisitos-do-sistema)
2. [Instalação Inicial](#instalação-inicial)
3. [Configuração do Ambiente](#configuração-do-ambiente)
4. [Configuração do Servidor Web](#configuração-do-servidor-web)
5. [Configuração de Segurança](#configuração-de-segurança)
6. [Scripts de Automação](#scripts-de-automação)
7. [Deploy em Produção](#deploy-em-produção)
8. [Monitoramento e Manutenção](#monitoramento-e-manutenção)
9. [Troubleshooting](#troubleshooting)

---

## 🖥️ Requisitos do Sistema

### Requisitos Mínimos
- **PHP**: 7.4 ou superior
- **MySQL**: 5.7 ou superior (ou MariaDB 10.2+)
- **Servidor Web**: Apache 2.4+ ou Nginx 1.18+
- **Extensões PHP**:
  - `pdo`
  - `pdo_mysql`
  - `mbstring`
  - `json`
  - `openssl`
  - `zip` (para backup)
  - `curl` (opcional)

### Requisitos Recomendados
- **PHP**: 8.0 ou superior
- **MySQL**: 8.0 ou superior
- **Memória RAM**: 2GB mínimo
- **Espaço em Disco**: 10GB mínimo
- **SSL/HTTPS**: Certificado válido

---

## 🚀 Instalação Inicial

### 1. Clone do Repositório
```bash
git clone [URL_DO_REPOSITORIO]
cd bichosdobairro-php
```

### 2. Instalar Dependências
```bash
composer install
```

### 3. Configurar Ambiente
```bash
# Copiar arquivo de exemplo
cp env.example .env

# Editar configurações
nano .env
```

### 4. Executar Script de Configuração
```bash
php setup.php
```

---

## ⚙️ Configuração do Ambiente

### Arquivo .env

O arquivo `.env` contém todas as configurações do sistema:

```env
# ========================================
# CONFIGURAÇÕES DO BANCO DE DADOS
# ========================================
DB_HOST=xmysql.bichosdobairro.com.br
DB_NAME=bichosdobairro5
DB_USER=bichosdobairro5
DB_PASS=!BdoB.1179!
DB_CHARSET=utf8mb4
DB_PORT=3306

# ========================================
# CONFIGURAÇÕES DA APLICAÇÃO
# ========================================
APP_NAME="Bichos do Bairro"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_VERSION=1.0.0

# ========================================
# CONFIGURAÇÕES DE SEGURANÇA
# ========================================
APP_KEY=base64:sua_chave_secreta_aqui_32_caracteres
SESSION_DRIVER=file
SESSION_LIFETIME=120
CSRF_TOKEN_LIFETIME=60

# ========================================
# CONFIGURAÇÕES DE LOG
# ========================================
LOG_CHANNEL=file
LOG_LEVEL=debug
LOG_MAX_FILES=30

# ========================================
# CONFIGURAÇÕES DE CACHE
# ========================================
CACHE_DRIVER=file
CACHE_TTL=3600
```

### Configurações por Ambiente

#### Desenvolvimento
```env
APP_ENV=development
APP_DEBUG=true
DEVELOPMENT_MODE=true
SHOW_ERRORS=true
```

#### Produção
```env
APP_ENV=production
APP_DEBUG=false
DEVELOPMENT_MODE=false
SHOW_ERRORS=false
```

---

## 🌐 Configuração do Servidor Web

### Apache

#### 1. Configuração Virtual Host
```apache
<VirtualHost *:80>
    ServerName bichosdobairro.local
    DocumentRoot /var/www/html/bichosdobairro-php/public
    
    <Directory /var/www/html/bichosdobairro-php/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/bichosdobairro_error.log
    CustomLog ${APACHE_LOG_DIR}/bichosdobairro_access.log combined
</VirtualHost>
```

#### 2. Habilitar Módulos
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo systemctl restart apache2
```

#### 3. Arquivo .htaccess
O arquivo `.htaccess` já está configurado na pasta `public/` com:
- Segurança
- Cache de assets
- Compressão GZIP
- Headers de segurança
- Configurações de PHP

### Nginx

#### 1. Configuração do Site
```bash
# Copiar arquivo de configuração
sudo cp nginx.conf /etc/nginx/sites-available/bichosdobairro

# Habilitar site
sudo ln -s /etc/nginx/sites-available/bichosdobairro /etc/nginx/sites-enabled/

# Testar configuração
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

#### 2. Configuração PHP-FPM
```bash
# Instalar PHP-FPM
sudo apt install php8.1-fpm

# Configurar pool
sudo nano /etc/php/8.1/fpm/pool.d/www.conf

# Reiniciar PHP-FPM
sudo systemctl restart php8.1-fpm
```

---

## 🔒 Configuração de Segurança

### 1. Permissões de Arquivos
```bash
# Diretórios que precisam de escrita
chmod 755 logs/
chmod 755 backups/
chmod 755 cache/
chmod 755 uploads/

# Arquivos sensíveis
chmod 600 .env
chmod 600 composer.lock
```

### 2. Firewall
```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# iptables (CentOS/RHEL)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 3. SSL/HTTPS (Let's Encrypt)
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache

# Gerar certificado
sudo certbot --apache -d seu-dominio.com

# Renovação automática
sudo crontab -e
# Adicionar: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 4. Configurações de Segurança PHP
```ini
; php.ini
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

---

## 🤖 Scripts de Automação

### 1. Script de Configuração (`setup.php`)
```bash
# Executar configuração completa
php setup.php
```

**Funcionalidades:**
- Verifica requisitos do sistema
- Cria diretórios necessários
- Configura arquivo .env
- Testa conexão com banco
- Verifica permissões
- Testa funcionalidades

### 2. Script de Backup (`scripts/backup.php`)
```bash
# Fazer backup
php scripts/backup.php backup

# Listar backups
php scripts/backup.php list

# Restaurar backup
php scripts/backup.php restore backup_2024-01-15_10-30-00.sql.gz

# Ver estatísticas
php scripts/backup.php stats
```

### 3. Script de Deploy (`scripts/deploy.php`)
```bash
# Executar deploy
php scripts/deploy.php deploy

# Verificar status
php scripts/deploy.php status

# Fazer rollback
php scripts/deploy.php rollback
```

---

## 🚀 Deploy em Produção

### 1. Preparação do Servidor
```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependências
sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-mbstring php8.1-json php8.1-openssl php8.1-zip composer git

# Configurar MySQL
sudo mysql_secure_installation
```

### 2. Configuração do Projeto
```bash
# Clonar projeto
cd /var/www/
sudo git clone [URL_DO_REPOSITORIO] bichosdobairro-php
cd bichosdobairro-php

# Configurar permissões
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod 600 .env

# Instalar dependências
composer install --no-dev --optimize-autoloader
```

### 3. Configuração do Banco
```bash
# Criar banco e usuário
mysql -u root -p
CREATE DATABASE bichosdobairro5;
CREATE USER 'bichosdobairro5'@'localhost' IDENTIFIED BY '!BdoB.1179!';
GRANT ALL PRIVILEGES ON bichosdobairro5.* TO 'bichosdobairro5'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importar estrutura
mysql -u bichosdobairro5 -p bichosdobairro5 < sql/database.sql
```

### 4. Configuração do Servidor Web
```bash
# Apache
sudo cp public/.htaccess /var/www/html/bichosdobairro-php/public/
sudo a2enmod rewrite headers expires deflate
sudo systemctl restart apache2

# Nginx
sudo cp nginx.conf /etc/nginx/sites-available/bichosdobairro
sudo ln -s /etc/nginx/sites-available/bichosdobairro /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl restart nginx
```

### 5. Configuração de Produção
```bash
# Executar deploy
php scripts/deploy.php deploy

# Configurar backup automático
sudo crontab -e
# Adicionar: 0 2 * * * cd /var/www/bichosdobairro-php && php scripts/backup.php backup
```

---

## 📊 Monitoramento e Manutenção

### 1. Logs do Sistema
```bash
# Logs da aplicação
tail -f logs/app.log

# Logs de erro
tail -f logs/error.log

# Logs do servidor web
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/nginx/error.log
```

### 2. Monitoramento de Performance
```bash
# Verificar uso de memória
free -h

# Verificar uso de disco
df -h

# Verificar processos PHP
ps aux | grep php

# Verificar conexões MySQL
mysql -u root -p -e "SHOW PROCESSLIST;"
```

### 3. Backup e Restauração
```bash
# Backup manual
php scripts/backup.php backup

# Verificar backups
php scripts/backup.php list

# Restaurar backup
php scripts/backup.php restore backup_2024-01-15_10-30-00.sql.gz
```

### 4. Manutenção Regular
```bash
# Limpar cache
php -r "require 'src/init.php'; Cache::clear();"

# Limpar logs antigos
find logs/ -name "*.log" -mtime +30 -delete

# Atualizar dependências
composer update

# Verificar segurança
php scripts/deploy.php status
```

---

## 🔧 Troubleshooting

### Problemas Comuns

#### 1. Erro de Conexão com Banco
```bash
# Verificar se MySQL está rodando
sudo systemctl status mysql

# Verificar configurações no .env
cat .env | grep DB_

# Testar conexão
mysql -u bichosdobairro5 -p -h xmysql.bichosdobairro.com.br
```

#### 2. Erro de Permissões
```bash
# Corrigir permissões
sudo chown -R www-data:www-data /var/www/bichosdobairro-php
sudo chmod -R 755 /var/www/bichosdobairro-php
sudo chmod 600 /var/www/bichosdobairro-php/.env
```

#### 3. Erro de Cache
```bash
# Limpar cache
php -r "require 'src/init.php'; Cache::clear();"

# Verificar permissões do diretório cache
ls -la cache/
```

#### 4. Erro de Logs
```bash
# Verificar se diretório logs existe
ls -la logs/

# Criar se não existir
mkdir -p logs/
chmod 755 logs/
```

#### 5. Erro de Upload
```bash
# Verificar configurações PHP
php -i | grep upload

# Aumentar limites no php.ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### Logs de Debug

#### 1. Ativar Debug
```env
APP_DEBUG=true
SHOW_ERRORS=true
LOG_LEVEL=debug
```

#### 2. Verificar Logs
```bash
# Logs da aplicação
tail -f logs/app.log

# Logs de erro
tail -f logs/error.log

# Logs do PHP
tail -f logs/php_errors.log
```

#### 3. Testar Funcionalidades
```bash
# Testar conexão com banco
php public/teste-conexao.php

# Testar funcionalidades
php public/teste-melhorias.php

# Verificar status do sistema
php scripts/deploy.php status
```

---

## 📞 Suporte

### Contatos
- **Desenvolvedor**: [Seu Nome]
- **Email**: [seu-email@exemplo.com]
- **Telefone**: [Seu Telefone]

### Recursos Adicionais
- [Documentação PHP](https://www.php.net/docs.php)
- [Documentação MySQL](https://dev.mysql.com/doc/)
- [Documentação Apache](https://httpd.apache.org/docs/)
- [Documentação Nginx](https://nginx.org/en/docs/)

---

## 📝 Changelog

### Versão 1.0.0 (2024-01-15)
- Configuração inicial do ambiente
- Scripts de automação
- Documentação completa
- Configurações de segurança
- Sistema de backup automático

---

**Última atualização**: 15/01/2024
**Versão da documentação**: 1.0.0 