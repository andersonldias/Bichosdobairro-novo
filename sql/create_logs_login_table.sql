-- Tabela de logs de login dos usuários
CREATE TABLE IF NOT EXISTS logs_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    sucesso BOOLEAN NOT NULL DEFAULT FALSE,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    detalhes TEXT,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_email (email),
    INDEX idx_sucesso (sucesso),
    INDEX idx_data_hora (data_hora),
    INDEX idx_ip_address (ip_address),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
); 