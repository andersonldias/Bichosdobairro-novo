# Correções de Bugs - Sistema Bichos do Bairro

## Bugs Identificados e Corrigidos

### 1. **Bug no campo 'idade' dos pets**
- **Problema**: O campo `idade` estava sendo enviado como string vazia (`''`) quando deveria ser `NULL` para o banco de dados
- **Erro**: `Incorrect integer value: '' for column 'idade' at row 1`
- **Arquivos Corrigidos**:
  - `src/Cliente.php` - Métodos `criar()` e `atualizar()`
  - `src/Pet.php` - Métodos `criar()` e `atualizar()`

**Correção Aplicada**:
```php
// Tratar idade: converter string vazia para NULL
$idade = $pet['idade'];
if ($idade === '' || $idade === null) {
    $idade = null;
} else {
    $idade = intval($idade);
}
```

### 2. **Bug na coluna 'status' dos agendamentos**
- **Problema**: A coluna `status` não existia na tabela `agendamentos` do banco de dados
- **Erro**: `Unknown column 'status' in 'field list'`
- **Arquivos Criados**:
  - `sql/update_agendamentos_status.sql` - Script SQL para adicionar a coluna
  - `public/corrigir-banco.php` - Script PHP para executar correções automaticamente

**Correção Aplicada**:
```sql
ALTER TABLE agendamentos ADD COLUMN status VARCHAR(30) DEFAULT 'Pendente' AFTER servico
```

## Como Aplicar as Correções

### Opção 1: Executar o script automático
1. Acesse: `http://localhost/corrigir-banco.php`
2. O script irá:
   - Verificar se a coluna `status` existe na tabela `agendamentos`
   - Adicionar a coluna se necessário
   - Testar a inserção de pets com idade NULL
   - Mostrar a estrutura das tabelas

### Opção 2: Executar manualmente no banco
1. Execute o script SQL: `sql/update_agendamentos_status.sql`
2. As correções no código PHP já foram aplicadas

## Verificação das Correções

### Teste 1: Cadastro de Pet sem Idade
1. Acesse o cadastro de clientes
2. Adicione um pet sem preencher a idade
3. Verifique se não há erro de banco de dados

### Teste 2: Cadastro de Agendamento
1. Acesse o cadastro de agendamentos
2. Crie um novo agendamento
3. Verifique se o campo status é salvo corretamente

## Arquivos Modificados

### Código PHP
- `src/Cliente.php` - Melhorado tratamento do campo idade
- `src/Pet.php` - Melhorado tratamento do campo idade

### Scripts SQL
- `sql/update_agendamentos_status.sql` - Novo script para adicionar coluna status
- `public/corrigir-banco.php` - Novo script para correções automáticas

## Status das Correções

✅ **Bug 1**: Campo idade dos pets - CORRIGIDO
✅ **Bug 2**: Coluna status dos agendamentos - CORRIGIDO

## Próximos Passos

1. Execute o script `corrigir-banco.php` para aplicar as correções no banco
2. Teste o cadastro de clientes com pets
3. Teste o cadastro de agendamentos
4. Remova os arquivos de debug após confirmar que tudo está funcionando

## Arquivos de Debug (podem ser removidos após testes)

- `public/debug_pets_error.txt`
- `public/debug_agendamento.txt`
- `public/debug_post_completo.txt`
- `public/debug_agendamento_lista.php`
- `public/debug-test.php`
- `public/debug_pets_insert.txt`
- `public/debug_pets_error.txt` 