# Mudanças no Cadastro de Clientes

## Resumo das Alterações

As seguintes mudanças foram implementadas no sistema de cadastro de clientes:

### ✅ E-mail Opcional
- O campo e-mail agora é **opcional** no cadastro de clientes
- Clientes podem ser cadastrados sem informar e-mail
- Validação de formato de e-mail só ocorre se o campo for preenchido
- E-mails vazios são armazenados como `NULL` no banco de dados

### ✅ Telefone Obrigatório
- O campo telefone agora é **obrigatório** no cadastro de clientes
- Todos os clientes devem ter pelo menos um telefone cadastrado
- Validação de formato de telefone (10 ou 11 dígitos)
- Verificação de duplicidade de telefone

## Arquivos Modificados

### 1. Banco de Dados
- **`sql/update_clientes_email_opcional.sql`**: Script SQL para modificar a estrutura da tabela
- **`public/aplicar-mudancas-clientes.php`**: Script para aplicar as mudanças no banco

### 2. Classe Cliente (`src/Cliente.php`)
- **Método `criar()`**: Adicionada validação de telefone obrigatório
- **Método `atualizar()`**: Adicionada validação de telefone obrigatório
- **Método `emailExiste()`**: Atualizado para considerar e-mail opcional
- **Método `existeDuplicado()`**: Atualizado para considerar e-mail opcional

### 3. Formulários
- **`public/clientes-debug.php`**: Telefone já estava como obrigatório, e-mail como opcional
- **`public/clientes-wizard.php`**: Removido `required` do campo e-mail
- **`public/validar-campo.php`**: Atualizada validação de e-mail para ser opcional

## Como Aplicar as Mudanças

### 1. Executar o Script de Migração
```bash
# Acesse o arquivo no navegador
http://localhost/bichosdobairro-php/public/aplicar-mudancas-clientes.php
```

### 2. Verificar se há Registros sem Telefone
O script irá:
- Verificar se existem registros sem telefone
- Mostrar quais registros precisam ser atualizados
- Aplicar as mudanças apenas se não houver registros sem telefone

### 3. Atualizar Registros sem Telefone (se necessário)
Se existirem registros sem telefone:
1. Acesse a lista de clientes
2. Edite cada cliente sem telefone
3. Adicione um telefone válido
4. Execute novamente o script de migração

## Estrutura da Tabela Após as Mudanças

```sql
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NULL,           -- OPCIONAL
    telefone VARCHAR(20) NOT NULL,     -- OBRIGATÓRIO
    cpf VARCHAR(14) NOT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Validações Implementadas

### E-mail (Opcional)
- Se preenchido, deve ter formato válido
- Se vazio, é aceito sem validação
- Verificação de duplicidade apenas se preenchido

### Telefone (Obrigatório)
- Campo obrigatório no cadastro
- Deve ter 10 ou 11 dígitos
- Verificação de duplicidade
- Formatação automática (se implementada)

## Benefícios das Mudanças

1. **Flexibilidade**: Clientes podem ser cadastrados mesmo sem e-mail
2. **Contato Garantido**: Todos os clientes terão pelo menos um telefone
3. **Melhor UX**: Formulários mais intuitivos com campos claramente marcados
4. **Integridade**: Validações apropriadas para cada tipo de campo

## Compatibilidade

- ✅ Compatível com registros existentes
- ✅ Migração automática de e-mails vazios para NULL
- ✅ Índices otimizados para busca por telefone
- ✅ Validações JavaScript e PHP atualizadas

## Testes Recomendados

1. **Cadastro sem e-mail**: Verificar se aceita cliente sem e-mail
2. **Cadastro sem telefone**: Verificar se rejeita cliente sem telefone
3. **E-mail duplicado**: Verificar se aceita e-mails duplicados quando vazios
4. **Telefone duplicado**: Verificar se rejeita telefones duplicados
5. **Edição de cliente**: Verificar se mantém as validações na edição

## Suporte

Para dúvidas ou problemas com as mudanças, consulte:
- Logs do sistema em `logs/`
- Validações em `public/validar-campo.php`
- Classe Cliente em `src/Cliente.php`


