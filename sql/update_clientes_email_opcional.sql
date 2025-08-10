-- Script para modificar a tabela clientes
-- Torna o e-mail opcional e garante que o telefone seja obrigatório
-- Sistema Bichos do Bairro

-- Modificar a coluna email para permitir NULL (opcional)
ALTER TABLE clientes MODIFY COLUMN email varchar(100) NULL;

-- Modificar a coluna telefone para ser NOT NULL (obrigatório)
ALTER TABLE clientes MODIFY COLUMN telefone varchar(20) NOT NULL;

-- Adicionar comentário explicativo
ALTER TABLE clientes 
MODIFY COLUMN email varchar(100) NULL COMMENT 'E-mail do cliente (opcional)',
MODIFY COLUMN telefone varchar(20) NOT NULL COMMENT 'Telefone do cliente (obrigatório)';

-- Verificar se há registros sem telefone e atualizar se necessário
-- (Isso é apenas uma verificação, não deve haver registros sem telefone após a mudança)
SELECT COUNT(*) as registros_sem_telefone 
FROM clientes 
WHERE telefone IS NULL OR telefone = '';

-- Verificar se há registros com e-mail vazio e converter para NULL
UPDATE clientes 
SET email = NULL 
WHERE email = '' OR email IS NULL;

-- Adicionar índice para otimizar buscas por telefone
ALTER TABLE clientes ADD INDEX idx_telefone (telefone);

-- Verificar a estrutura final da tabela
DESCRIBE clientes;
