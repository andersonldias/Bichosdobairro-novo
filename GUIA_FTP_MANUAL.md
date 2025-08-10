# 🚀 GUIA DE ENVIO VIA FTP - BICHOS DO BAIRRO

## ✅ **BANCO IMPORTADO COM SUCESSO!**

Agora vamos enviar os arquivos do sistema via FTP.

---

## 📁 **ARQUIVOS PARA ENVIAR**

### **Estrutura de Pastas:**
```
bichosdobairro/
├── public/          (arquivos públicos do site)
├── src/             (código fonte PHP)
├── vendor/          (dependências Composer)
├── sql/             (scripts SQL)
├── composer.json    (configuração Composer)
├── composer.lock    (versões fixas)
├── env.example      (exemplo de configuração)
├── config-producao.env (configuração de produção)
├── .htaccess        (configuração Apache)
└── README.md        (documentação)
```

### **Total de Arquivos:** 675 arquivos

---

## 🛠️ **OPÇÕES DE ENVIO**

### **OPÇÃO 1: Envio Manual (RECOMENDADO)**

#### **1.1 Compactar Manualmente**
```
1. Selecione todas as pastas e arquivos
2. Clique com botão direito
3. Escolha "Enviar para" > "Pasta compactada"
4. Nome: bichosdobairro.zip
```

#### **1.2 Ou usar WinRAR/7-Zip**
```
1. Instalar WinRAR ou 7-Zip
2. Selecionar arquivos
3. Clicar em "Adicionar ao arquivo"
4. Formato: ZIP
5. Nome: bichosdobairro.zip
```

### **OPÇÃO 2: Envio Direto (Arquivo por Arquivo)**

#### **2.1 Conectar via FTP**
```
Host: (dados da hospedagem)
Usuário: (dados da hospedagem)
Senha: (dados da hospedagem)
Porta: 21
```

#### **2.2 Enviar Pastas**
```
1. public/ → /public_html/
2. src/ → /public_html/src/
3. vendor/ → /public_html/vendor/
4. sql/ → /public_html/sql/
```

#### **2.3 Enviar Arquivos da Raiz**
```
1. composer.json → /public_html/
2. composer.lock → /public_html/
3. env.example → /public_html/
4. config-producao.env → /public_html/
5. .htaccess → /public_html/
6. README.md → /public_html/
```

---

## 🚀 **PASSO A PASSO COMPLETO**

### **PASSO 1: Preparar Arquivos**
```
1. Compactar sistema em ZIP
2. Verificar se todos os arquivos estão incluídos
3. Tamanho esperado: ~10-20 MB
```

### **PASSO 2: Conectar FTP**
```
1. Abrir cliente FTP (FileZilla, WinSCP, etc.)
2. Inserir dados da hospedagem:
   - Host: (ex: ftp.seudominio.com)
   - Usuário: (ex: seudominio_user)
   - Senha: (senha da hospedagem)
   - Porta: 21
3. Conectar
```

### **PASSO 3: Navegar para Pasta do Site**
```
1. Navegar para pasta do site
2. Geralmente: /public_html/ ou /www/
3. Ou pasta específica do domínio
```

### **PASSO 4: Fazer Backup (se necessário)**
```
1. Se já existe um site:
   - Renomear pasta atual para: site_old
   - Ou fazer backup via painel da hospedagem
```

### **PASSO 5: Enviar Arquivo ZIP**
```
1. Fazer upload do arquivo: bichosdobairro.zip
2. Aguardar conclusão do upload
3. Verificar se o upload foi bem-sucedido
```

### **PASSO 6: Extrair no Servidor**
```
1. Via painel da hospedagem:
   - Acessar File Manager
   - Selecionar arquivo ZIP
   - Clicar em "Extract"
   - Escolher pasta de destino

2. Ou via SSH:
   unzip bichosdobairro.zip
```

### **PASSO 7: Configurar Sistema**
```
1. Copiar config-producao.env para .env
2. Editar .env com dados corretos:
   - DB_HOST=localhost
   - DB_NAME=bichosdobairro
   - DB_USER=(usuário do banco)
   - DB_PASS=(senha do banco)
   - APP_URL=https://seudominio.com
```

### **PASSO 8: Verificar Permissões**
```
1. Pasta logs/: 755
2. Arquivo .env: 644
3. Outros arquivos: 644
4. Pasta public/: 755
```

### **PASSO 9: Testar Sistema**
```
1. Acessar: https://seudominio.com
2. Login: admin
3. Senha: admin123
4. Verificar funcionalidades:
   - Clientes
   - Pets
   - Agendamentos
   - Relatórios
```

---

## 📋 **CHECKLIST DE ENVIO**

- [ ] Sistema compactado em ZIP
- [ ] Backup do site atual (se existir)
- [ ] Upload via FTP concluído
- [ ] Arquivo extraído no servidor
- [ ] Arquivo .env configurado
- [ ] Permissões verificadas
- [ ] Sistema testado
- [ ] Login funcionando
- [ ] Funcionalidades verificadas

---

## ⚠️ **IMPORTANTE**

### **Antes do Envio:**
- ✅ Banco de dados importado
- ✅ Credenciais do banco confirmadas
- ✅ Backup do site atual (se existir)

### **Durante o Envio:**
- ✅ Verificar conexão FTP
- ✅ Aguardar upload completo
- ✅ Confirmar extração

### **Após o Envio:**
- ✅ Configurar .env
- ✅ Verificar permissões
- ✅ Testar sistema
- ✅ Verificar logs de erro

---

## 🆘 **PROBLEMAS COMUNS**

### **Erro de Upload:**
```
- Verificar espaço em disco
- Verificar permissões FTP
- Tentar upload em partes menores
```

### **Erro de Extração:**
```
- Verificar se ZIP não está corrompido
- Tentar extrair via SSH
- Verificar permissões de escrita
```

### **Erro de Conexão:**
```
- Verificar credenciais FTP
- Verificar host e porta
- Tentar cliente FTP diferente
```

### **Erro 500:**
```
- Verificar arquivo .env
- Verificar permissões
- Verificar logs de erro
- Confirmar credenciais do banco
```

---

## 🎯 **COMANDOS ÚTEIS**

### **Via SSH (se disponível):**
```bash
# Conectar via SSH
ssh usuario@seudominio.com

# Navegar para pasta
cd public_html

# Extrair ZIP
unzip bichosdobairro.zip

# Verificar permissões
chmod 755 logs/
chmod 644 .env

# Testar PHP
php -v
```

### **Verificar Logs:**
```bash
# Logs do sistema
tail -f logs/error.log

# Logs do servidor
tail -f /var/log/apache2/error.log
```

---

## 🎉 **RESUMO FINAL**

### **Status Atual:**
- ✅ **Banco importado**
- 🔄 **Próximo:** Enviar arquivos via FTP

### **Ação Necessária:**
1. **Compactar sistema** em ZIP
2. **Conectar FTP** com dados da hospedagem
3. **Enviar arquivo** para pasta do site
4. **Extrair** no servidor
5. **Configurar** arquivo .env
6. **Testar** sistema

**🚀 QUASE LÁ! Só falta enviar os arquivos via FTP! 🎉** 