# Instalação Automática do Sistema de Login

## 🚀 Instalação Rápida

### Windows
1. **Duplo clique** no arquivo `instalar-login.bat`
2. Aguarde a instalação automática
3. Acesse: `http://localhost/public/login.php`

### Linux/Mac
1. **Abra o terminal** no diretório do projeto
2. Execute: `./instalar-login.sh`
3. Acesse: `http://localhost/public/login.php`

### Linha de Comando (Qualquer Sistema)
1. **Abra o terminal** no diretório do projeto
2. Execute: `php instalar-login-automatico.php`
3. Acesse: `http://localhost/public/login.php`

## 📋 Pré-requisitos

### ✅ Obrigatórios
- **PHP 7.4+** instalado e no PATH
- **MySQL/MariaDB** configurado
- **Arquivo .env** configurado com credenciais do banco
- **Permissões** de escrita no banco de dados

### 🔧 Verificações
- [ ] PHP instalado: `php --version`
- [ ] MySQL rodando: `mysql --version`
- [ ] Arquivo .env configurado
- [ ] Conexão com banco funcionando

## 🔐 Credenciais Padrão

Após a instalação, use as credenciais padrão:

- **Email**: `admin@bichosdobairro.com`
- **Senha**: `admin123`

⚠️ **IMPORTANTE**: Altere a senha padrão imediatamente após o primeiro login!

## 📁 Arquivos Criados

### Tabelas do Banco
- `usuarios` - Usuários do sistema
- `logs_login` - Logs de tentativas de login
- `logs_atividade` - Logs de atividades dos usuários

### Scripts de Instalação
- `instalar-login-automatico.php` - Script PHP principal
- `instalar-login.bat` - Script para Windows
- `instalar-login.sh` - Script para Linux/Mac

## 🛠️ Instalação Manual (Se Necessário)

Se os scripts automáticos não funcionarem, execute manualmente:

### 1. Conectar ao MySQL
```bash
mysql -u seu_usuario -p
```

### 2. Executar SQL
```sql
-- Criar tabela de usuários
source sql/create_usuarios_table.sql;

-- Criar tabela de logs
source sql/create_logs_atividade_table.sql;
```

### 3. Verificar Instalação
```sql
SHOW TABLES;
SELECT * FROM usuarios;
```

## 🔍 Solução de Problemas

### ❌ "PHP não encontrado"
**Solução:**
- Windows: Baixe PHP em https://windows.php.net/download/
- Linux: `sudo apt-get install php php-mysql`
- Mac: `brew install php`

### ❌ "Arquivo não encontrado"
**Solução:**
- Certifique-se de estar no diretório raiz do projeto
- Verifique se todos os arquivos estão presentes

### ❌ "Erro de conexão com banco"
**Solução:**
- Verifique o arquivo `.env`
- Confirme se o MySQL está rodando
- Teste a conexão manualmente

### ❌ "Permissão negada"
**Solução:**
- Linux/Mac: `chmod +x instalar-login.sh`
- Windows: Execute como administrador

## 📊 Verificação da Instalação

### 1. Verificar Tabelas
```sql
SHOW TABLES LIKE 'usuarios';
SHOW TABLES LIKE 'logs_login';
SHOW TABLES LIKE 'logs_atividade';
```

### 2. Verificar Usuário Admin
```sql
SELECT id, nome, email, nivel_acesso FROM usuarios;
```

### 3. Testar Login
- Acesse: `http://localhost/public/login.php`
- Use as credenciais padrão
- Deve redirecionar para o dashboard

## 🔒 Segurança Pós-Instalação

### 1. Alterar Senha Padrão
- Acesse: `http://localhost/public/alterar-senha.php`
- Use uma senha forte (mínimo 8 caracteres)

### 2. Configurar SSL/HTTPS
- Instale certificado SSL
- Configure redirecionamento HTTPS

### 3. Configurar Backup
- Configure backup automático do banco
- Mantenha cópias dos logs

### 4. Monitorar Logs
```sql
-- Ver tentativas de login recentes
SELECT * FROM logs_login 
WHERE data_hora > DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY data_hora DESC;

-- Ver usuários bloqueados
SELECT nome, email, tentativas_login, bloqueado_ate 
FROM usuarios 
WHERE bloqueado_ate IS NOT NULL;
```

## 📞 Suporte

### Logs de Erro
- Verifique: `logs/error.log`
- Console do navegador (F12)
- Logs do servidor web

### Informações Úteis
- Versão do PHP: `php --version`
- Versão do MySQL: `mysql --version`
- Configuração: arquivo `.env`
- Permissões: `ls -la` (Linux/Mac)

## ✅ Checklist de Instalação

- [ ] PHP instalado e funcionando
- [ ] MySQL configurado e acessível
- [ ] Arquivo .env configurado
- [ ] Script de instalação executado
- [ ] Tabelas criadas no banco
- [ ] Usuário admin criado
- [ ] Login funcionando
- [ ] Senha padrão alterada
- [ ] SSL configurado (produção)
- [ ] Backup configurado

## 🎉 Próximos Passos

1. **Teste o login** com as credenciais padrão
2. **Altere a senha** imediatamente
3. **Configure as demais páginas** do sistema
4. **Teste todas as funcionalidades**
5. **Configure backup** e monitoramento
6. **Deploy em produção** com SSL

---

**Sistema de Login Seguro - Bichos do Bairro**  
✅ Pronto para uso em produção! 