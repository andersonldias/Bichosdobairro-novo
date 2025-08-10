-- ========================================
-- Tabela para Monitoramento de Uso da API Google Maps
-- Sistema Bichos do Bairro
-- ========================================

-- Criar tabela de uso mensal da API Google Maps
CREATE TABLE IF NOT EXISTS google_maps_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    month_year VARCHAR(7) NOT NULL UNIQUE COMMENT 'Formato: YYYY-MM',
    usage_count INT NOT NULL DEFAULT 0 COMMENT 'Número de consultas realizadas no mês',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última atualização do contador',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do registro',
    
    -- Índices para otimização
    INDEX idx_month_year (month_year),
    INDEX idx_last_updated (last_updated)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Monitoramento de uso mensal da API Google Maps';

-- Inserir registro para o mês atual se não existir
INSERT IGNORE INTO google_maps_usage (month_year, usage_count) 
VALUES (DATE_FORMAT(NOW(), '%Y-%m'), 0);

-- ========================================
-- View para estatísticas de uso
-- ========================================

CREATE OR REPLACE VIEW v_google_maps_stats AS
SELECT 
    month_year,
    usage_count,
    CASE 
        WHEN month_year = DATE_FORMAT(NOW(), '%Y-%m') THEN 'Mês Atual'
        WHEN month_year = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m') THEN 'Mês Anterior'
        ELSE 'Histórico'
    END as periodo,
    ROUND((usage_count / 10000) * 100, 2) as percentual_limite,
    (10000 - usage_count) as consultas_restantes,
    last_updated,
    created_at
FROM google_maps_usage
ORDER BY month_year DESC;

-- ========================================
-- Procedure para limpeza de dados antigos
-- ========================================

DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_cleanup_google_maps_usage()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_month_year VARCHAR(7);
    DECLARE cur CURSOR FOR 
        SELECT month_year 
        FROM google_maps_usage 
        WHERE STR_TO_DATE(CONCAT(month_year, '-01'), '%Y-%m-%d') < DATE_SUB(NOW(), INTERVAL 12 MONTH);
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Manter apenas os últimos 12 meses de dados
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_month_year;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        DELETE FROM google_maps_usage WHERE month_year = v_month_year;
    END LOOP;
    
    CLOSE cur;
    
    -- Log da limpeza
    SELECT CONCAT('Limpeza concluída. Registros mantidos: ', COUNT(*)) as resultado
    FROM google_maps_usage;
END//

DELIMITER ;

-- ========================================
-- Event para limpeza automática mensal
-- ========================================

-- Habilitar event scheduler se não estiver ativo
-- SET GLOBAL event_scheduler = ON;

-- Criar event para limpeza automática (comentado por padrão)
/*
CREATE EVENT IF NOT EXISTS ev_cleanup_google_maps_usage
ON SCHEDULE EVERY 1 MONTH
STARTS CONCAT(DATE_FORMAT(NOW(), '%Y-%m'), '-01 02:00:00')
DO
  CALL sp_cleanup_google_maps_usage();
*/

-- ========================================
-- Consultas úteis para monitoramento
-- ========================================

-- Ver estatísticas do mês atual
/*
SELECT * FROM v_google_maps_stats WHERE periodo = 'Mês Atual';
*/

-- Ver histórico completo
/*
SELECT * FROM v_google_maps_stats;
*/

-- Ver uso dos últimos 6 meses
/*
SELECT 
    month_year,
    usage_count,
    ROUND((usage_count / 10000) * 100, 2) as percentual,
    last_updated
FROM google_maps_usage 
WHERE STR_TO_DATE(CONCAT(month_year, '-01'), '%Y-%m-%d') >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
ORDER BY month_year DESC;
*/

-- Verificar se há meses próximos do limite
/*
SELECT 
    month_year,
    usage_count,
    ROUND((usage_count / 10000) * 100, 2) as percentual,
    CASE 
        WHEN usage_count >= 10000 THEN '🚫 Limite Atingido'
        WHEN usage_count >= 8000 THEN '⚠️ Próximo do Limite'
        WHEN usage_count >= 5000 THEN '⚡ Uso Moderado'
        ELSE '✅ Uso Normal'
    END as status
FROM google_maps_usage 
WHERE usage_count > 0
ORDER BY usage_count DESC;
*/