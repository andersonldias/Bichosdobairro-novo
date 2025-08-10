-- Adicionar tabela para múltiplos telefones
CREATE TABLE IF NOT EXISTS telefones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    nome VARCHAR(50) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Migrar telefones existentes para a nova tabela
INSERT INTO telefones (cliente_id, nome, numero)
SELECT id, 'Principal', telefone 
FROM clientes 
WHERE telefone IS NOT NULL AND telefone != '';

-- Remover coluna telefone da tabela clientes (opcional - manter por compatibilidade)
-- ALTER TABLE clientes DROP COLUMN telefone; 