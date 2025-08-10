-- Script para adicionar a coluna status na tabela agendamentos
-- Execute este script se a coluna status não existir na tabela

USE bichosdobairro;

-- Verificar se a coluna status existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'bichosdobairro'
    AND TABLE_NAME = 'agendamentos'
    AND COLUMN_NAME = 'status'
);

-- Adicionar a coluna status se ela não existir
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE agendamentos ADD COLUMN status VARCHAR(30) DEFAULT "Pendente" AFTER servico',
    'SELECT "Coluna status já existe na tabela agendamentos" as resultado'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 