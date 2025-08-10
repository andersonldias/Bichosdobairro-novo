-- Tabela de usuários para sistema de login
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

-- Inserir usuário administrador padrão
-- Senha: admin123 (deve ser alterada após primeiro login)
INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES 
('Administrador', 'admin@bichosdobairro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Tabela de logs de login para auditoria
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