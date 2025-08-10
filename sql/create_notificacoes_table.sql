-- Tabela de Notificações
-- Sistema Bichos do Bairro

CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo ENUM('lembrete', 'vencido', 'novo_cliente', 'novo_pet', 'cliente', 'sistema') NOT NULL DEFAULT 'sistema',
    dados_extra JSON,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_lida (lida),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar colunas de timestamp nas tabelas existentes se não existirem
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE pets 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE agendamentos 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Adicionar colunas adicionais na tabela pets
ALTER TABLE pets 
ADD COLUMN IF NOT EXISTS peso DECIMAL(5,2) NULL COMMENT 'Peso em kg',
ADD COLUMN IF NOT EXISTS data_nascimento DATE NULL,
ADD COLUMN IF NOT EXISTS ultima_vacinacao DATE NULL,
ADD COLUMN IF NOT EXISTS observacoes TEXT NULL;

-- Adicionar colunas adicionais na tabela agendamentos
ALTER TABLE agendamentos 
ADD COLUMN IF NOT EXISTS observacoes TEXT NULL;

-- Inserir algumas notificações de exemplo
INSERT INTO notificacoes (titulo, mensagem, tipo, dados_extra) VALUES
('Sistema Ativado', 'Sistema de notificações ativado com sucesso!', 'sistema', '{"sistema": "notificacoes"}'),
('Bem-vindo', 'Bem-vindo ao sistema Bichos do Bairro!', 'sistema', '{"sistema": "boas_vindas"}');

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_clientes_nome ON clientes(nome);
CREATE INDEX IF NOT EXISTS idx_clientes_email ON clientes(email);
CREATE INDEX IF NOT EXISTS idx_clientes_telefone ON clientes(telefone);
CREATE INDEX IF NOT EXISTS idx_clientes_cpf ON clientes(cpf);

CREATE INDEX IF NOT EXISTS idx_pets_nome ON pets(nome);
CREATE INDEX IF NOT EXISTS idx_pets_especie ON pets(especie);
CREATE INDEX IF NOT EXISTS idx_pets_cliente_id ON pets(cliente_id);

CREATE INDEX IF NOT EXISTS idx_agendamentos_data ON agendamentos(data);
CREATE INDEX IF NOT EXISTS idx_agendamentos_hora ON agendamentos(hora);
CREATE INDEX IF NOT EXISTS idx_agendamentos_status ON agendamentos(status);
CREATE INDEX IF NOT EXISTS idx_agendamentos_cliente_id ON agendamentos(cliente_id);
CREATE INDEX IF NOT EXISTS idx_agendamentos_pet_id ON agendamentos(pet_id);
CREATE INDEX IF NOT EXISTS idx_agendamentos_servico ON agendamentos(servico); 