# Solução de Problemas - Tela de Login

## 🔍 Diagnóstico Rápido

### 1. **Teste de Conexão**
Acesse: `http://localhost:8000/teste-login.php`

Este arquivo vai mostrar:
- ✅ Se o PHP está funcionando
- ✅ Se a conexão com banco está OK
- ✅ Se as tabelas foram criadas
- ✅ Se o usuário admin existe

### 2. **Login Simplificado**
Acesse: `http://localhost:8000/login-simples.php`

Esta é uma versão mais simples do login que:
- Não usa Tailwind CSS (evita problemas de CDN)
- Tem CSS inline (não depende de arquivos externos)
- Mostra credenciais na tela
- Tem tratamento de erros mais claro

## 🚨 Problemas Comuns e Soluções

### ❌ **"Página não encontrada"**

**Possíveis causas:**
1. Servidor não está rodando
2. Porta incorreta
3. Caminho errado

**Soluções:**
```bash
# 1. Iniciar servidor PHP
php -S localhost:8000 -t public

# 2. Verificar se está rodando
curl http://localhost:8000/teste-login.php

# 3. Verificar porta
netstat -an | findstr :8000  # Windows
netstat -an | grep :8000     # Linux/Mac
```

### ❌ **"Erro de conexão com banco"**

**Verificar arquivo .env:**
```env
DB_HOST=localhost
DB_NAME=bichosdobairro
DB_USER=seu_usuario
DB_PASS=sua_senha
DB_PORT=3306
```

**Testar conexão manual:**
```bash
mysql -u seu_usuario -p -h localhost bichosdobairro
```

### ❌ **"Tabelas não encontradas"**

**Executar instalação novamente:**
```bash
php instalar-login-automatico.php
```

**Ou manualmente:**
```sql
-- Conectar ao MySQL
mysql -u root -p

-- Selecionar banco
USE bichosdobairro;

-- Verificar tabelas
SHOW TABLES;

-- Se não existirem, criar:
source sql/create_usuarios_table.sql;
source sql/create_logs_atividade_table.sql;
```

### ❌ **"Usuário admin não encontrado"**

**Verificar se existe:**
```sql
SELECT * FROM usuarios WHERE email = 'admin@bichosdobairro.com';
```

**Se não existir, criar manualmente:**
```sql
INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES 
('Administrador', 'admin@bichosdobairro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

### ❌ **"Erro de permissão"**

**Windows:**
- Execute o terminal como administrador
- Verifique permissões de pasta

**Linux/Mac:**
```bash
chmod +x instalar-login.sh
chmod 644 public/*.php
chmod 644 src/*.php
```

### ❌ **"Erro de sessão"**

**Verificar configurações PHP:**
```php
// Adicionar no início do arquivo
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();
```

**Verificar diretório de sessão:**
```bash
# Windows
dir C:\Windows\Temp

# Linux/Mac
ls -la /tmp
```

## 🔧 Testes Passo a Passo

### **Teste 1: Servidor PHP**
```bash
# Terminal 1
php -S localhost:8000 -t public

# Terminal 2
curl http://localhost:8000/teste-login.php
```

### **Teste 2: Banco de Dados**
```bash
# Conectar ao MySQL
mysql -u root -p

# Verificar banco
SHOW DATABASES;
USE bichosdobairro;
SHOW TABLES;
SELECT * FROM usuarios;
```

### **Teste 3: Arquivos PHP**
```bash
# Verificar se arquivos existem
ls -la src/
ls -la public/
ls -la sql/

# Testar inclusão
php -l src/init.php
php -l src/Auth.php
php -l public/login-simples.php
```

### **Teste 4: Login Funcional**
1. Acesse: `http://localhost:8000/login-simples.php`
2. Use credenciais: `admin@bichosdobairro.com` / `admin123`
3. Deve redirecionar para dashboard

## 📋 Checklist de Verificação

### ✅ **Pré-requisitos**
- [ ] PHP 7.4+ instalado
- [ ] MySQL/MariaDB rodando
- [ ] Arquivo .env configurado
- [ ] Permissões de arquivo corretas

### ✅ **Instalação**
- [ ] Tabelas criadas no banco
- [ ] Usuário admin criado
- [ ] Arquivos PHP sem erros de sintaxe

### ✅ **Funcionamento**
- [ ] Servidor PHP rodando
- [ ] Página de teste acessível
- [ ] Login funcionando
- [ ] Redirecionamento OK

## 🆘 Comandos de Emergência

### **Reinstalar Tudo**
```bash
# 1. Parar servidor (Ctrl+C)
# 2. Reinstalar sistema
php instalar-login-automatico.php

# 3. Reiniciar servidor
php -S localhost:8000 -t public

# 4. Testar
curl http://localhost:8000/teste-login.php
```

### **Reset Manual do Banco**
```sql
-- Conectar ao MySQL
mysql -u root -p

-- Dropar e recriar banco
DROP DATABASE IF EXISTS bichosdobairro;
CREATE DATABASE bichosdobairro;
USE bichosdobairro;

-- Executar scripts
source sql/create_usuarios_table.sql;
source sql/create_logs_atividade_table.sql;

-- Verificar
SHOW TABLES;
SELECT * FROM usuarios;
```

### **Verificar Logs**
```bash
# Logs do PHP
tail -f logs/error.log

# Logs do MySQL
tail -f /var/log/mysql/error.log

# Logs do servidor web
tail -f /var/log/apache2/error.log
```

## 📞 Suporte

### **Informações Úteis**
- Versão do PHP: `php --version`
- Versão do MySQL: `mysql --version`
- Configuração: arquivo `.env`
- Permissões: `ls -la` (Linux/Mac) ou `dir` (Windows)

### **Arquivos de Teste**
- `public/teste-login.php` - Teste de conexão
- `public/login-simples.php` - Login simplificado
- `public/dashboard.php` - Dashboard de teste

### **Logs Importantes**
- `logs/error.log` - Erros do sistema
- Console do navegador (F12) - Erros JavaScript
- Logs do servidor web - Erros de servidor

---

**Se ainda tiver problemas, execute o teste de conexão e me envie o resultado!** 