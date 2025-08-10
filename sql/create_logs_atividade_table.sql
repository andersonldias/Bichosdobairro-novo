-- Tabela de logs de atividade dos usuários
CREATE TABLE IF NOT EXISTS logs_atividade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    detalhes TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_acao (acao),
    INDEX idx_data_hora (data_hora),
    INDEX idx_ip_address (ip_address),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
); 