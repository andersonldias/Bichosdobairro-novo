# 🔧 SOLUÇÃO PARA ERRO DE FOREIGN KEY
## Erro #1451 - Não pode apagar uma linha pai

---

## 🚨 **PROBLEMA IDENTIFICADO**

### **Erro:**
```
#1451 - Não pode apagar uma linha pai: uma restrição de chave estrangeira falhou
```

### **Causa:**
- Existem **chaves estrangeiras (foreign keys)** que impedem a exclusão
- A tabela `usuarios` é referenciada por outras tabelas
- O MySQL protege a integridade dos dados

---

## ✅ **SOLUÇÕES DISPONÍVEIS**

### **SOLUÇÃO 1: Script SQL Seguro (RECOMENDADO)**

#### **1.1 Usar o script seguro:**
```sql
-- Execute este comando primeiro:
SET FOREIGN_KEY_CHECKS = 0;

-- Depois execute o script completo:
-- (Use o arquivo: sql/limpar_banco_seguro.sql)
```

#### **1.2 Ou execute diretamente:**
```sql
-- Desabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 0;

-- Apagar todas as tabelas
DROP TABLE IF EXISTS agendamentos_recorrentes_ocorrencias;
DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS agendamentos_recorrentes;
DROP TABLE IF EXISTS logs_agendamentos_recorrentes;
DROP TABLE IF EXISTS logs_atividade;
DROP TABLE IF EXISTS logs_login;
DROP TABLE IF EXISTS usuarios_permissoes;
DROP TABLE IF EXISTS nivel_permissoes;
DROP TABLE IF EXISTS telefones;
DROP TABLE IF EXISTS pets;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS permissoes;
DROP TABLE IF EXISTS niveis_acesso;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS notificacoes;
DROP TABLE IF EXISTS configuracoes;

-- Reabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 1;
```

### **SOLUÇÃO 2: Apagar Banco Inteiro**

#### **2.1 Via phpMyAdmin:**
```
1. Acesse phpMyAdmin
2. Selecione o banco de dados
3. Clique em "Operações"
4. Role até "Apagar banco de dados"
5. Digite o nome do banco para confirmar
6. Clique em "OK"
7. Recrie o banco:
   - Nome: bichosdobairro
   - Collation: utf8mb4_unicode_ci
```

#### **2.2 Via SQL:**
```sql
-- Apagar banco completo
DROP DATABASE bichosdobairro;

-- Recriar banco
CREATE DATABASE bichosdobairro 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### **SOLUÇÃO 3: Script PHP Automatizado**

#### **3.1 Execute o script:**
```bash
php limpar-e-importar-banco.php backups/backup_completo_2025-07-19_20-19-55.sql
```

---

## 📋 **PASSO A PASSO NO phpMyAdmin**

### **PASSO 1: Desabilitar Foreign Keys**
```sql
-- No phpMyAdmin, aba SQL, execute:
SET FOREIGN_KEY_CHECKS = 0;
```

### **PASSO 2: Apagar Tabelas**
```sql
-- Execute o script completo:
-- (Copie e cole o conteúdo do arquivo sql/limpar_banco_seguro.sql)
```

### **PASSO 3: Verificar Limpeza**
```sql
-- Verificar se as tabelas foram apagadas:
SHOW TABLES;
```

### **PASSO 4: Reabilitar Foreign Keys**
```sql
-- Reabilitar verificação:
SET FOREIGN_KEY_CHECKS = 1;
```

### **PASSO 5: Importar Backup**
```
1. Clique na aba "Importar"
2. Selecione o arquivo: backup_completo_2025-07-19_20-19-55.sql
3. Configure:
   - Formato: SQL
   - Codificação: utf-8
4. Clique em "Executar"
```

---

## 🔍 **VERIFICAÇÃO PÓS-LIMPEZA**

### **Verificar se o banco está limpo:**
```sql
SHOW TABLES;
-- Deve retornar uma lista vazia
```

### **Verificar se a importação funcionou:**
```sql
-- Verificar tabelas criadas:
SHOW TABLES;

-- Verificar dados:
SELECT COUNT(*) as total_usuarios FROM usuarios;
SELECT COUNT(*) as total_clientes FROM clientes;
SELECT COUNT(*) as total_pets FROM pets;
SELECT COUNT(*) as total_agendamentos FROM agendamentos;
```

---

## ⚠️ **IMPORTANTE**

### **Antes de Limpar:**
- ✅ **Faça backup** do banco atual (se necessário)
- ✅ **Confirme** que você quer apagar tudo
- ✅ **Tenha o arquivo de backup** pronto

### **Durante a Limpeza:**
- ✅ **Desabilite foreign keys** primeiro
- ✅ **Apague todas as tabelas**
- ✅ **Reabilite foreign keys** depois

### **Após a Importação:**
- ✅ **Verifique** se todas as tabelas foram criadas
- ✅ **Teste** o sistema
- ✅ **Configure** o arquivo .env

---

## 🛠️ **COMANDOS RÁPIDOS**

### **Para Limpar Rapidamente:**
```sql
-- Execute estes comandos em sequência:
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS agendamentos_recorrentes_ocorrencias, agendamentos, agendamentos_recorrentes, logs_agendamentos_recorrentes, logs_atividade, logs_login, usuarios_permissoes, nivel_permissoes, telefones, pets, clientes, permissoes, niveis_acesso, usuarios, notificacoes, configuracoes;
SET FOREIGN_KEY_CHECKS = 1;
```

### **Para Verificar:**
```sql
SHOW TABLES;
```

---

## 📞 **SE AINDA DER ERRO**

### **Opção 1: Usar Script PHP**
```bash
php limpar-e-importar-banco.php backups/backup_completo_2025-07-19_20-19-55.sql
```

### **Opção 2: Apagar Banco Inteiro**
```sql
DROP DATABASE bichosdobairro;
CREATE DATABASE bichosdobairro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **Opção 3: Linha de Comando**
```bash
mysql -u usuario -p nome_banco < sql/limpar_banco_seguro.sql
mysql -u usuario -p nome_banco < backups/backup_completo_2025-07-19_20-19-55.sql
```

---

## 🎯 **RESUMO**

### **Para resolver o erro #1451:**

1. **Execute primeiro:** `SET FOREIGN_KEY_CHECKS = 0;`
2. **Depois apague as tabelas**
3. **Reabilite:** `SET FOREIGN_KEY_CHECKS = 1;`
4. **Importe o backup**

### **Ou use o script automatizado:**
```bash
php limpar-e-importar-banco.php backups/backup_completo_2025-07-19_20-19-55.sql
```

---

**🎉 PROBLEMA RESOLVIDO! 🎉**

Após seguir estes passos, o banco será limpo sem erros de foreign key! 