# 🗄️ GUIA COMPLETO - phpMyAdmin
## Como Apagar e Importar o Banco de Dados

---

## 📋 **PRÉ-REQUISITOS**

### **Arquivos Necessários:**
- ✅ Backup do banco: `backups/backup_completo_2025-07-19_20-19-55.sql`
- ✅ Acesso ao phpMyAdmin do novo servidor
- ✅ Credenciais de acesso ao banco

---

## 🚨 **ATENÇÃO: BACKUP ANTES DE APAGAR**

### **1. Fazer Backup do Banco Atual (SE EXISTIR)**
```sql
-- No phpMyAdmin, vá em:
1. Selecione o banco de dados
2. Clique em "Exportar"
3. Escolha "Personalizado"
4. Marque "Adicionar DROP TABLE"
5. Clique em "Executar"
6. Salve o arquivo como "backup_antes_limpeza.sql"
```

---

## 🗑️ **PASSO 1: APAGAR DADOS DO BANCO**

### **Opção A: Apagar Tabelas Individuais**
```sql
-- No phpMyAdmin SQL:
1. Acesse o banco de dados
2. Clique na aba "SQL"
3. Execute os comandos:

-- Desabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 0;

-- Apagar todas as tabelas
DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS agendamentos_recorrentes;
DROP TABLE IF EXISTS agendamentos_recorrentes_ocorrencias;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS configuracoes;
DROP TABLE IF EXISTS logs_agendamentos_recorrentes;
DROP TABLE IF EXISTS logs_atividade;
DROP TABLE IF EXISTS logs_login;
DROP TABLE IF EXISTS nivel_permissoes;
DROP TABLE IF EXISTS niveis_acesso;
DROP TABLE IF EXISTS notificacoes;
DROP TABLE IF EXISTS permissoes;
DROP TABLE IF EXISTS pets;
DROP TABLE IF EXISTS telefones;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS usuarios_permissoes;

-- Reabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 1;
```

### **Opção B: Apagar Banco Inteiro e Recriar**
```sql
-- 1. Apagar banco completo
DROP DATABASE bichosdobairro;

-- 2. Recriar banco
CREATE DATABASE bichosdobairro 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### **Opção C: Via Interface phpMyAdmin**
```
1. Acesse phpMyAdmin
2. Selecione o banco de dados
3. Clique em "Operações"
4. Role até "Apagar banco de dados"
5. Digite o nome do banco para confirmar
6. Clique em "OK"
7. Recrie o banco com:
   - Nome: bichosdobairro
   - Collation: utf8mb4_unicode_ci
```

---

## 📥 **PASSO 2: IMPORTAR O BACKUP**

### **Método 1: Via Interface phpMyAdmin**

#### **2.1 Preparar o Arquivo**
```
1. Abra o arquivo: backups/backup_completo_2025-07-19_20-19-55.sql
2. Verifique se o arquivo está correto (deve começar com comentários)
3. Se necessário, renomeie para: backup_bichosdobairro.sql
```

#### **2.2 Importar no phpMyAdmin**
```
1. Acesse phpMyAdmin
2. Selecione o banco de dados "bichosdobairro"
3. Clique na aba "Importar"
4. Clique em "Escolher arquivo"
5. Selecione: backup_completo_2025-07-19_20-19-55.sql
6. Configure as opções:
   - Formato: SQL
   - Codificação: utf-8
   - Tamanho máximo: Aumente se necessário (ex: 50MB)
7. Clique em "Executar"
```

#### **2.3 Verificar Importação**
```
1. Após a importação, verifique:
   - Mensagem de sucesso
   - Número de consultas executadas
   - Tabelas criadas na lista lateral
2. Clique em "Estrutura" para ver as tabelas
3. Verifique se todas as 16 tabelas estão presentes
```

### **Método 2: Via SQL (Colar Código)**

#### **2.1 Copiar Conteúdo do Backup**
```
1. Abra o arquivo: backups/backup_completo_2025-07-19_20-19-55.sql
2. Selecione todo o conteúdo (Ctrl+A)
3. Copie (Ctrl+C)
```

#### **2.2 Colar no phpMyAdmin**
```
1. Acesse phpMyAdmin
2. Selecione o banco de dados
3. Clique na aba "SQL"
4. Cole o conteúdo (Ctrl+V)
5. Clique em "Executar"
```

---

## ✅ **PASSO 3: VERIFICAÇÃO PÓS-IMPORTAÇÃO**

### **3.1 Verificar Tabelas**
```sql
-- Execute no SQL do phpMyAdmin:
SHOW TABLES;

-- Deve retornar 16 tabelas:
- agendamentos
- agendamentos_recorrentes
- agendamentos_recorrentes_ocorrencias
- clientes
- configuracoes
- logs_agendamentos_recorrentes
- logs_atividade
- logs_login
- nivel_permissoes
- niveis_acesso
- notificacoes
- permissoes
- pets
- telefones
- usuarios
- usuarios_permissoes
```

### **3.2 Verificar Dados**
```sql
-- Verificar usuários:
SELECT COUNT(*) as total_usuarios FROM usuarios;

-- Verificar clientes:
SELECT COUNT(*) as total_clientes FROM clientes;

-- Verificar pets:
SELECT COUNT(*) as total_pets FROM pets;

-- Verificar agendamentos:
SELECT COUNT(*) as total_agendamentos FROM agendamentos;
```

### **3.3 Verificar Estrutura**
```sql
-- Verificar estrutura de uma tabela importante:
DESCRIBE usuarios;
DESCRIBE clientes;
DESCRIBE agendamentos;
```

---

## 🔧 **PASSO 4: CONFIGURAÇÃO DO SISTEMA**

### **4.1 Atualizar Arquivo .env**
```env
# Editar o arquivo .env com as credenciais do novo banco:
DB_HOST=localhost
DB_NAME=bichosdobairro
DB_USER=seu_usuario
DB_PASS=sua_senha
DB_CHARSET=utf8mb4
DB_PORT=3306
```

### **4.2 Testar Conexão**
```php
// Execute o script de verificação:
php verificar-producao.php
```

---

## ⚠️ **PROBLEMAS COMUNS E SOLUÇÕES**

### **Problema 1: Arquivo muito grande**
```
Solução:
1. Aumente o limite no phpMyAdmin:
   - Vá em "Configurações" > "Importar"
   - Aumente "Tamanho máximo"
2. Ou divida o arquivo em partes menores
3. Ou use linha de comando MySQL
```

### **Problema 2: Erro de codificação**
```
Solução:
1. Verifique se o arquivo está em UTF-8
2. Configure a codificação correta no phpMyAdmin
3. Use "utf8mb4" como collation
```

### **Problema 3: Timeout durante importação**
```
Solução:
1. Aumente o tempo limite no phpMyAdmin
2. Execute em partes menores
3. Use linha de comando MySQL
```

### **Problema 4: Erro de permissões**
```
Solução:
1. Verifique se o usuário tem permissões:
   - CREATE
   - INSERT
   - SELECT
   - DROP (se necessário)
2. Conceda permissões se necessário
```

---

## 🛠️ **MÉTODO ALTERNATIVO: LINHA DE COMANDO**

### **Se o phpMyAdmin não funcionar:**
```bash
# 1. Acesse o servidor via SSH
# 2. Navegue até a pasta do backup
cd /caminho/para/backup

# 3. Execute o comando MySQL
mysql -u usuario -p nome_banco < backup_completo_2025-07-19_20-19-55.sql

# 4. Verifique a importação
mysql -u usuario -p nome_banco -e "SHOW TABLES;"
```

---

## 📊 **CHECKLIST DE VERIFICAÇÃO**

### **✅ Após Apagar:**
- [ ] Banco limpo ou recriado
- [ ] Nenhuma tabela antiga presente

### **✅ Após Importar:**
- [ ] 16 tabelas criadas
- [ ] 2 usuários importados
- [ ] 2 clientes importados
- [ ] 4 pets importados
- [ ] 5 agendamentos importados
- [ ] Estrutura das tabelas correta

### **✅ Após Configurar:**
- [ ] Arquivo .env atualizado
- [ ] Conexão testada
- [ ] Sistema funcionando
- [ ] Login possível

---

## 🎯 **RESUMO DOS PASSOS**

### **1. Backup (SE NECESSÁRIO)**
- Fazer backup do banco atual (se existir)

### **2. Limpar Banco**
- Apagar tabelas ou banco completo
- Recriar banco se necessário

### **3. Importar Backup**
- Via phpMyAdmin ou linha de comando
- Verificar importação

### **4. Configurar Sistema**
- Atualizar .env
- Testar conexão
- Verificar funcionamento

---

## 📞 **SUPORTE**

### **Se algo der errado:**
1. **Verifique os logs** do phpMyAdmin
2. **Use o backup original** como referência
3. **Execute o script de verificação:** `php verificar-producao.php`
4. **Consulte a documentação:** `INSTRUCOES_TRANSFERENCIA_COMPLETA.md`

### **Arquivos de Apoio:**
- `backup_completo_2025-07-19_20-19-55.sql` - Backup completo
- `restaurar-banco.php` - Script de restauração
- `verificar-producao.php` - Verificação do sistema

---

**🎉 SUCESSO NA IMPORTAÇÃO! 🎉**

Após seguir estes passos, o banco estará completamente restaurado e pronto para uso! 