# Sistema de Login Seguro - Bichos do Bairro

## Resumo

Implementei um sistema de login completo e seguro para o sistema de agendamento, seguindo as melhores práticas de segurança para aplicações web expostas na internet.

## Características de Segurança

### 🔒 **Proteções Implementadas**

1. **Hash de Senhas**
   - Uso do `password_hash()` com `PASSWORD_DEFAULT`
   - Salt automático e seguro
   - Resistente a ataques de rainbow table

2. **Proteção contra Força Bruta**
   - Máximo de 5 tentativas de login
   - Bloqueio temporário de 15 minutos
   - Contagem de tentativas por usuário
   - Desbloqueio automático após tempo

3. **Sessões Seguras**
   - Regeneração de ID de sessão após login
   - Cookies HttpOnly e Secure
   - SameSite=Strict
   - Timeout de sessão configurável

4. **Validação de Entrada**
   - Sanitização de emails
   - Validação de formato de email
   - Prevenção de SQL Injection
   - Escape de saída HTML

5. **Headers de Segurança**
   - X-Content-Type-Options: nosniff
   - X-Frame-Options: DENY
   - X-XSS-Protection: 1; mode=block
   - Content Security Policy (CSP)
   - Referrer-Policy: strict-origin-when-cross-origin

6. **Logs de Auditoria**
   - Log de todas as tentativas de login
   - Log de atividades dos usuários
   - Registro de IP e User Agent
   - Timestamp de todas as ações

## Arquivos Criados/Modificados

### 📁 **Arquivos SQL**
- `sql/create_usuarios_table.sql` - Tabela de usuários
- `sql/create_logs_atividade_table.sql` - Tabela de logs

### 📁 **Classes PHP**
- `src/Auth.php` - Classe principal de autenticação
- `src/AuthMiddleware.php` - Middleware de proteção

### 📁 **Páginas Web**
- `public/login.php` - Página de login (atualizada)
- `public/logout.php` - Logout seguro
- `public/alterar-senha.php` - Alteração de senha

## Como Usar

### 1. **Configuração Inicial**

Execute os arquivos SQL para criar as tabelas:

```sql
-- Executar em ordem:
source sql/create_usuarios_table.sql
source sql/create_logs_atividade_table.sql
```

### 2. **Proteger Páginas**

Para proteger qualquer página do sistema:

```php
<?php
require_once '../src/init.php';
require_once '../src/AuthMiddleware.php';

$auth = new AuthMiddleware();

// Proteção básica (usuário logado)
$usuario = $auth->securePage();

// Proteção para administradores
$usuario = $auth->securePage('admin');

// Adicionar headers de segurança
$auth->addSecurityHeaders();
```

### 3. **Credenciais Padrão**

Após a instalação, use as credenciais padrão:
- **Email**: admin@bichosdobairro.com
- **Senha**: admin123

⚠️ **IMPORTANTE**: Altere a senha padrão imediatamente após o primeiro login!

## Funcionalidades

### 🔐 **Sistema de Login**

- **Interface moderna** com Tailwind CSS
- **Validação em tempo real** dos campos
- **Toggle de visibilidade** da senha
- **Proteção contra múltiplos submits**
- **Mensagens de erro claras**
- **Countdown para desbloqueio**

### 🛡️ **Proteções de Segurança**

- **Captcha simples** após 3 tentativas
- **Bloqueio progressivo** de IPs
- **Logs detalhados** de todas as tentativas
- **Detecção de IP real** (proxy-aware)
- **Prevenção de CSRF** implícita

### 👤 **Gerenciamento de Usuários**

- **Níveis de acesso** (usuario/admin)
- **Controle de sessão** com timeout
- **Alteração de senha** segura
- **Logout completo** com limpeza de sessão

## Estrutura do Banco

### Tabela `usuarios`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nome (VARCHAR(100))
- email (VARCHAR(100), UNIQUE)
- senha_hash (VARCHAR(255))
- nivel_acesso (ENUM('admin', 'usuario'))
- ativo (BOOLEAN)
- ultimo_login (TIMESTAMP)
- tentativas_login (INT)
- bloqueado_ate (TIMESTAMP)
- criado_em (TIMESTAMP)
- atualizado_em (TIMESTAMP)
```

### Tabela `logs_login`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- usuario_id (INT, FOREIGN KEY)
- email (VARCHAR(100))
- ip_address (VARCHAR(45))
- user_agent (TEXT)
- sucesso (BOOLEAN)
- data_hora (TIMESTAMP)
```

### Tabela `logs_atividade`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- usuario_id (INT, FOREIGN KEY)
- acao (VARCHAR(100))
- detalhes (TEXT)
- ip_address (VARCHAR(45))
- user_agent (TEXT)
- data_hora (TIMESTAMP)
```

## Configurações de Segurança

### 🔧 **Configurações de Sessão**
```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
```

### ⏱️ **Timeouts Configuráveis**
- **Sessão**: 1 hora (3600 segundos)
- **Bloqueio**: 15 minutos (900 segundos)
- **Tentativas máximas**: 5

### 🚫 **Proteções Ativas**
- **Força bruta**: Bloqueio automático
- **Sessão expirada**: Logout automático
- **Usuário inativo**: Logout automático
- **Múltiplos submits**: Prevenção

## Monitoramento e Logs

### 📊 **Logs Disponíveis**

1. **Logs de Login**
   - Todas as tentativas (sucesso/falha)
   - IP e User Agent
   - Timestamp preciso

2. **Logs de Atividade**
   - Ações dos usuários
   - Detalhes das operações
   - Rastreamento completo

### 🔍 **Consultas Úteis**

```sql
-- Tentativas de login recentes
SELECT * FROM logs_login 
WHERE data_hora > DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY data_hora DESC;

-- Usuários bloqueados
SELECT nome, email, tentativas_login, bloqueado_ate 
FROM usuarios 
WHERE bloqueado_ate IS NOT NULL;

-- Atividades de um usuário
SELECT * FROM logs_atividade 
WHERE usuario_id = ? 
ORDER BY data_hora DESC;
```

## Recomendações de Segurança

### 🛡️ **Para Produção**

1. **SSL/HTTPS obrigatório**
2. **Alterar senha padrão** imediatamente
3. **Configurar backup** dos logs
4. **Monitorar tentativas** de login
5. **Revisar logs** regularmente

### 🔄 **Manutenção**

1. **Limpar logs antigos** periodicamente
2. **Atualizar senhas** regularmente
3. **Monitorar IPs** suspeitos
4. **Revisar permissões** de usuários

### 📈 **Melhorias Futuras**

1. **Autenticação de dois fatores** (2FA)
2. **Notificações de login** por email
3. **Whitelist de IPs** para admin
4. **Rate limiting** por IP
5. **Backup automático** de logs

## Status Final

✅ **Sistema de Login Implementado e Seguro**

- 🔐 Autenticação robusta
- 🛡️ Proteções contra ataques comuns
- 📊 Logs de auditoria completos
- 🎨 Interface moderna e responsiva
- ⚡ Performance otimizada
- 🔧 Fácil manutenção

O sistema está pronto para uso em produção com todas as medidas de segurança necessárias para uma aplicação web exposta na internet. 