-- Tabela de ocorrências de agendamentos recorrentes
CREATE TABLE IF NOT EXISTS agendamentos_recorrentes_ocorrencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_recorrente_id INT NOT NULL,
    data_ocorrencia DATE NOT NULL,
    hora_ocorrencia TIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado', 'realizado', 'nao_compareceu') DEFAULT 'pendente',
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para performance
    INDEX idx_agendamento_recorrente_id (agendamento_recorrente_id),
    INDEX idx_data_ocorrencia (data_ocorrencia),
    INDEX idx_status (status),
    INDEX idx_data_hora (data_ocorrencia, hora_ocorrencia),
    
    -- Chave estrangeira
    FOREIGN KEY (agendamento_recorrente_id) REFERENCES agendamentos_recorrentes(id) ON DELETE CASCADE
);

-- Inserir comentário na tabela
ALTER TABLE agendamentos_recorrentes_ocorrencias COMMENT = 'Ocorrências geradas automaticamente dos agendamentos recorrentes'; 