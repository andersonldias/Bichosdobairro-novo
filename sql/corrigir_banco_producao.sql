-- Script para corrigir o banco de dados de produção
-- Execute este script no banco de dados bichosdobairro

USE bichosdobairro;

-- 1. Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'usuario') DEFAULT 'usuario',
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP NULL,
    tentativas_login INT DEFAULT 0,
    bloqueado_ate TIMESTAMP NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
);

-- 2. Criar tabela de logs de login
CREATE TABLE IF NOT EXISTS logs_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    sucesso BOOLEAN NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_email (email),
    INDEX idx_data_hora (data_hora),
    INDEX idx_sucesso (sucesso),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- 3. Criar tabela de níveis de acesso
CREATE TABLE IF NOT EXISTS niveis_acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    permissoes JSON,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Criar tabela de logs de atividade
CREATE TABLE IF NOT EXISTS logs_atividade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    acao VARCHAR(100) NOT NULL,
    tabela_afetada VARCHAR(50),
    registro_id INT,
    dados_anteriores JSON,
    dados_novos JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_acao (acao),
    INDEX idx_tabela_afetada (tabela_afetada),
    INDEX idx_data_hora (data_hora),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- 5. Inserir usuário administrador padrão
-- Senha: admin123 (deve ser alterada após primeiro login)
INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES 
('Administrador', 'admin@bichosdobairro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- 6. Inserir níveis de acesso padrão
INSERT IGNORE INTO niveis_acesso (nome, descricao, permissoes) VALUES 
('Administrador', 'Acesso total ao sistema', '["*"]'),
('Usuário', 'Acesso básico ao sistema', '["agendamentos.visualizar", "clientes.visualizar", "pets.visualizar"]');

-- 7. Verificar se as tabelas principais existem
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    telefone VARCHAR(20),
    endereco VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    especie VARCHAR(50),
    raca VARCHAR(50),
    idade INT,
    cliente_id INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    cliente_id INT NOT NULL,
    data DATE NOT NULL,
    hora TIME NOT NULL,
    servico VARCHAR(100) NOT NULL,
    status VARCHAR(30) DEFAULT 'Pendente',
    observacoes TEXT,
    recorrencia_id INT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- 8. Criar tabela de agendamentos recorrentes se não existir
CREATE TABLE IF NOT EXISTS agendamentos_recorrentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    pet_id INT NOT NULL,
    tipo_recorrencia ENUM('semanal', 'quinzenal', 'mensal') NOT NULL,
    dia_semana INT NOT NULL COMMENT '0=Domingo, 1=Segunda, ..., 6=Sábado',
    semana_mes INT NULL COMMENT '1=1ª semana, 2=2ª semana, etc. (apenas para mensal)',
    hora_inicio TIME NOT NULL,
    duracao_minutos INT DEFAULT 60,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    servico VARCHAR(100) NOT NULL,
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    INDEX idx_ativo (ativo),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_tipo_recorrencia (tipo_recorrencia)
);

-- 9. Criar tabela de logs de agendamentos recorrentes
CREATE TABLE IF NOT EXISTS logs_agendamentos_recorrentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recorrencia_id INT NOT NULL,
    acao VARCHAR(50) NOT NULL,
    dados JSON,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorrencia_id) REFERENCES agendamentos_recorrentes(id) ON DELETE CASCADE,
    INDEX idx_recorrencia_id (recorrencia_id),
    INDEX idx_data_hora (data_hora)
);

-- 10. Verificar e mostrar status das tabelas
SELECT 'Tabelas criadas com sucesso!' as status;

-- Mostrar tabelas existentes
SHOW TABLES;

-- Mostrar usuário administrador criado
SELECT id, nome, email, nivel_acesso, ativo FROM usuarios WHERE nivel_acesso = 'admin'; 