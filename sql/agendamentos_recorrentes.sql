-- =====================================================
-- SISTEMA DE AGENDAMENTOS RECORRENTES - BICHOS DO BAIRRO
-- Versão 1.1 - Dezembro 2024
-- =====================================================

-- Tabela principal de agendamentos recorrentes
CREATE TABLE IF NOT EXISTS agendamentos_recorrentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    pet_id INT NOT NULL,
    tipo_recorrencia ENUM('semanal', 'quinzenal', 'mensal') NOT NULL,
    dia_semana INT NOT NULL COMMENT '1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado, 7=Domingo',
    semana_mes INT NULL COMMENT 'Para mensal: 1=1ª semana, 2=2ª semana, 3=3ª semana, 4=4ª semana, 5=última semana',
    hora_inicio TIME NOT NULL,
    duracao INT NOT NULL DEFAULT 60 COMMENT 'duração em minutos',
    data_inicio DATE NOT NULL,
    data_fim DATE NULL COMMENT 'NULL = indefinido',
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_cliente (cliente_id),
    INDEX idx_pet (pet_id),
    INDEX idx_tipo_recorrencia (tipo_recorrencia),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_ativo (ativo),
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar colunas na tabela de agendamentos existente
ALTER TABLE agendamentos 
ADD COLUMN IF NOT EXISTS recorrencia_id INT NULL COMMENT 'Referência ao agendamento recorrente',
ADD COLUMN IF NOT EXISTS data_original DATE NULL COMMENT 'Data original do agendamento recorrente',
ADD COLUMN IF NOT EXISTS status ENUM('confirmado', 'cancelado', 'remarcado') DEFAULT 'confirmado',
ADD COLUMN IF NOT EXISTS observacoes_edicao TEXT COMMENT 'Observações específicas desta ocorrência';

-- Adicionar índices para performance
ALTER TABLE agendamentos 
ADD INDEX IF NOT EXISTS idx_recorrencia (recorrencia_id),
ADD INDEX IF NOT EXISTS idx_status (status),
ADD INDEX IF NOT EXISTS idx_data_original (data_original);

-- Adicionar foreign key
ALTER TABLE agendamentos 
ADD CONSTRAINT fk_agendamento_recorrencia 
FOREIGN KEY (recorrencia_id) REFERENCES agendamentos_recorrentes(id) ON DELETE SET NULL;

-- Tabela de logs de agendamentos recorrentes
CREATE TABLE IF NOT EXISTS logs_agendamentos_recorrentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recorrencia_id INT NOT NULL,
    agendamento_id INT NULL,
    acao ENUM('criado', 'editado', 'cancelado', 'remarcado', 'pausado', 'reativado') NOT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dados_anteriores JSON NULL,
    dados_novos JSON NULL,
    usuario_id INT NULL,
    observacoes TEXT,
    
    INDEX idx_recorrencia (recorrencia_id),
    INDEX idx_agendamento (agendamento_id),
    INDEX idx_acao (acao),
    INDEX idx_data_acao (data_acao),
    
    FOREIGN KEY (recorrencia_id) REFERENCES agendamentos_recorrentes(id) ON DELETE CASCADE,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo (opcional)
INSERT INTO agendamentos_recorrentes 
(cliente_id, pet_id, tipo_recorrencia, dia_semana, semana_mes, hora_inicio, duracao, data_inicio, observacoes) 
VALUES 
(1, 1, 'semanal', 2, NULL, '09:00:00', 60, '2025-01-01', 'Consulta de rotina semanal'),
(1, 1, 'quinzenal', 5, NULL, '14:00:00', 30, '2025-01-01', 'Aplicação de medicamento quinzenal'),
(1, 1, 'mensal', 1, 1, '10:00:00', 90, '2025-01-01', 'Consulta mensal - primeira segunda-feira');

-- =====================================================
-- FUNÇÕES AUXILIARES (MySQL 8.0+)
-- =====================================================

-- Função para obter o nome do dia da semana
DELIMITER //
CREATE FUNCTION IF NOT EXISTS get_dia_semana_nome(dia INT) 
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE nome VARCHAR(20);
    CASE dia
        WHEN 1 THEN SET nome = 'Segunda-feira';
        WHEN 2 THEN SET nome = 'Terça-feira';
        WHEN 3 THEN SET nome = 'Quarta-feira';
        WHEN 4 THEN SET nome = 'Quinta-feira';
        WHEN 5 THEN SET nome = 'Sexta-feira';
        WHEN 6 THEN SET nome = 'Sábado';
        WHEN 7 THEN SET nome = 'Domingo';
        ELSE SET nome = 'Desconhecido';
    END CASE;
    RETURN nome;
END //
DELIMITER ;

-- Função para obter o nome da semana do mês
DELIMITER //
CREATE FUNCTION IF NOT EXISTS get_semana_mes_nome(semana INT) 
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE nome VARCHAR(20);
    CASE semana
        WHEN 1 THEN SET nome = '1ª semana';
        WHEN 2 THEN SET nome = '2ª semana';
        WHEN 3 THEN SET nome = '3ª semana';
        WHEN 4 THEN SET nome = '4ª semana';
        WHEN 5 THEN SET nome = 'última semana';
        ELSE SET nome = 'Desconhecida';
    END CASE;
    RETURN nome;
END //
DELIMITER ;

-- =====================================================
-- VIEWS ÚTEIS
-- =====================================================

-- View para agendamentos recorrentes com informações completas
CREATE OR REPLACE VIEW v_agendamentos_recorrentes AS
SELECT 
    ar.id,
    ar.cliente_id,
    c.nome as cliente_nome,
    ar.pet_id,
    p.nome as pet_nome,
    ar.tipo_recorrencia,
    ar.dia_semana,
    get_dia_semana_nome(ar.dia_semana) as dia_semana_nome,
    ar.semana_mes,
    get_semana_mes_nome(ar.semana_mes) as semana_mes_nome,
    ar.hora_inicio,
    ar.duracao,
    ar.data_inicio,
    ar.data_fim,
    ar.ativo,
    ar.observacoes,
    ar.created_at,
    ar.updated_at
FROM agendamentos_recorrentes ar
JOIN clientes c ON ar.cliente_id = c.id
JOIN pets p ON ar.pet_id = p.id
WHERE ar.ativo = TRUE;

-- View para próximos agendamentos recorrentes
CREATE OR REPLACE VIEW v_proximos_recorrentes AS
SELECT 
    ar.id,
    ar.cliente_id,
    c.nome as cliente_nome,
    ar.pet_id,
    p.nome as pet_nome,
    ar.tipo_recorrencia,
    ar.dia_semana,
    ar.semana_mes,
    ar.hora_inicio,
    ar.duracao,
    ar.data_inicio,
    ar.data_fim,
    ar.observacoes,
    -- Calcular próxima data baseada no tipo de recorrência
    CASE 
        WHEN ar.tipo_recorrencia = 'semanal' THEN
            DATE_ADD(CURDATE(), INTERVAL (ar.dia_semana - WEEKDAY(CURDATE()) + 7) % 7 DAY)
        WHEN ar.tipo_recorrencia = 'quinzenal' THEN
            DATE_ADD(CURDATE(), INTERVAL (ar.dia_semana - WEEKDAY(CURDATE()) + 14) % 14 DAY)
        WHEN ar.tipo_recorrencia = 'mensal' THEN
            -- Lógica para mensal (simplificada)
            DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
    END as proxima_data
FROM agendamentos_recorrentes ar
JOIN clientes c ON ar.cliente_id = c.id
JOIN pets p ON ar.pet_id = p.id
WHERE ar.ativo = TRUE 
AND (ar.data_fim IS NULL OR ar.data_fim >= CURDATE());

-- =====================================================
-- FIM DO SCRIPT
-- ===================================================== 