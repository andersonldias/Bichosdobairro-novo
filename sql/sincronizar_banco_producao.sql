-- =====================================================
-- SCRIPT DE SINCRONIZAÇÃO DO BANCO DE PRODUÇÃO
-- Sistema Bichos do Bairro
-- Data: 19/07/2025
-- =====================================================

-- Configurações iniciais
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

USE bichosdobairro;

-- =====================================================
-- 1. TABELA DE USUÁRIOS
-- =====================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `nivel_acesso` enum('admin','usuario') DEFAULT 'usuario',
  `ativo` tinyint(1) DEFAULT 1,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `tentativas_login` int(11) DEFAULT 0,
  `bloqueado_ate` timestamp NULL DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_nivel_acesso` (`nivel_acesso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABELA DE LOGS DE LOGIN
-- =====================================================
CREATE TABLE IF NOT EXISTS `logs_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `sucesso` tinyint(1) NOT NULL,
  `data_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_email` (`email`),
  KEY `idx_data_hora` (`data_hora`),
  KEY `idx_sucesso` (`sucesso`),
  CONSTRAINT `fk_logs_login_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TABELA DE NÍVEIS DE ACESSO
-- =====================================================
CREATE TABLE IF NOT EXISTS `niveis_acesso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `descricao` text,
  `permissoes` json DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABELA DE PERMISSÕES
-- =====================================================
CREATE TABLE IF NOT EXISTS `permissoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `area` varchar(50) NOT NULL,
  `acao` varchar(50) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  KEY `idx_area` (`area`),
  KEY `idx_acao` (`acao`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TABELA DE RELACIONAMENTO USUÁRIOS-PERMISSÕES
-- =====================================================
CREATE TABLE IF NOT EXISTS `usuarios_permissoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL,
  `concedido_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `concedido_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_permissao` (`usuario_id`,`permissao_id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_permissao_id` (`permissao_id`),
  CONSTRAINT `fk_usuarios_permissoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuarios_permissoes_permissao` FOREIGN KEY (`permissao_id`) REFERENCES `permissoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TABELA DE LOGS DE ATIVIDADE
-- =====================================================
CREATE TABLE IF NOT EXISTS `logs_atividade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `tabela_afetada` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `dados_anteriores` json DEFAULT NULL,
  `dados_novos` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `data_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_acao` (`acao`),
  KEY `idx_tabela_afetada` (`tabela_afetada`),
  KEY `idx_data_hora` (`data_hora`),
  CONSTRAINT `fk_logs_atividade_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. TABELA DE CLIENTES
-- =====================================================
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `observacoes` text,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nome` (`nome`),
  KEY `idx_email` (`email`),
  KEY `idx_cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. TABELA DE PETS
-- =====================================================
CREATE TABLE IF NOT EXISTS `pets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `especie` varchar(50) DEFAULT NULL,
  `raca` varchar(50) DEFAULT NULL,
  `idade` int(11) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `cliente_id` int(11) NOT NULL,
  `observacoes` text,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cliente_id` (`cliente_id`),
  KEY `idx_nome` (`nome`),
  KEY `idx_especie` (`especie`),
  CONSTRAINT `fk_pets_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. TABELA DE AGENDAMENTOS
-- =====================================================
CREATE TABLE IF NOT EXISTS `agendamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pet_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `servico` varchar(100) NOT NULL,
  `status` varchar(30) DEFAULT 'Pendente',
  `observacoes` text,
  `recorrencia_id` int(11) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pet_id` (`pet_id`),
  KEY `idx_cliente_id` (`cliente_id`),
  KEY `idx_data` (`data`),
  KEY `idx_status` (`status`),
  KEY `idx_recorrencia_id` (`recorrencia_id`),
  CONSTRAINT `fk_agendamentos_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agendamentos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. TABELA DE AGENDAMENTOS RECORRENTES
-- =====================================================
CREATE TABLE IF NOT EXISTS `agendamentos_recorrentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `tipo_recorrencia` enum('semanal','quinzenal','mensal') NOT NULL,
  `dia_semana` int(11) NOT NULL COMMENT '0=Domingo, 1=Segunda, ..., 6=Sábado',
  `semana_mes` int(11) DEFAULT NULL COMMENT '1=1ª semana, 2=2ª semana, etc. (apenas para mensal)',
  `hora_inicio` time NOT NULL,
  `duracao_minutos` int(11) DEFAULT 60,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `servico` varchar(100) NOT NULL,
  `observacoes` text,
  `ativo` tinyint(1) DEFAULT 1,
  `valor` decimal(10,2) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cliente_id` (`cliente_id`),
  KEY `idx_pet_id` (`pet_id`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_data_inicio` (`data_inicio`),
  KEY `idx_tipo_recorrencia` (`tipo_recorrencia`),
  CONSTRAINT `fk_agendamentos_recorrentes_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agendamentos_recorrentes_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. TABELA DE LOGS DE AGENDAMENTOS RECORRENTES
-- =====================================================
CREATE TABLE IF NOT EXISTS `logs_agendamentos_recorrentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recorrencia_id` int(11) NOT NULL,
  `acao` varchar(50) NOT NULL,
  `dados` json DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recorrencia_id` (`recorrencia_id`),
  KEY `idx_data_hora` (`data_hora`),
  CONSTRAINT `fk_logs_agendamentos_recorrentes_recorrencia` FOREIGN KEY (`recorrencia_id`) REFERENCES `agendamentos_recorrentes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. TABELA DE NOTIFICAÇÕES
-- =====================================================
CREATE TABLE IF NOT EXISTS `notificacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo` varchar(50) DEFAULT 'info',
  `dados_extra` json DEFAULT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lida_em` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_lida` (`lida`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_criado_em` (`criado_em`),
  CONSTRAINT `fk_notificacoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. TABELA DE TELEFONES (para múltiplos telefones por cliente)
-- =====================================================
CREATE TABLE IF NOT EXISTS `telefones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `tipo` varchar(20) DEFAULT 'celular',
  `principal` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cliente_id` (`cliente_id`),
  KEY `idx_numero` (`numero`),
  KEY `idx_principal` (`principal`),
  CONSTRAINT `fk_telefones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. TABELA DE CONFIGURAÇÕES DO SISTEMA
-- =====================================================
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text,
  `descricao` text,
  `tipo` varchar(20) DEFAULT 'string',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERÇÃO DE DADOS PADRÃO
-- =====================================================

-- Inserir usuário administrador padrão
INSERT IGNORE INTO `usuarios` (`nome`, `email`, `senha_hash`, `nivel_acesso`, `ativo`) VALUES 
('Administrador', 'admin@bichosdobairro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Inserir níveis de acesso padrão
INSERT IGNORE INTO `niveis_acesso` (`nome`, `descricao`, `permissoes`, `ativo`) VALUES 
('Administrador', 'Acesso total ao sistema', '["*"]', 1),
('Usuário', 'Acesso básico ao sistema', '["agendamentos.visualizar", "clientes.visualizar", "pets.visualizar"]', 1);

-- Inserir permissões padrão
INSERT IGNORE INTO `permissoes` (`nome`, `descricao`, `area`, `acao`, `ativo`) VALUES 
('agendamentos.visualizar', 'Visualizar agendamentos', 'agendamentos', 'visualizar', 1),
('agendamentos.criar', 'Criar agendamentos', 'agendamentos', 'criar', 1),
('agendamentos.editar', 'Editar agendamentos', 'agendamentos', 'editar', 1),
('agendamentos.excluir', 'Excluir agendamentos', 'agendamentos', 'excluir', 1),
('agendamentos_recorrentes.visualizar', 'Visualizar agendamentos recorrentes', 'agendamentos_recorrentes', 'visualizar', 1),
('agendamentos_recorrentes.criar', 'Criar agendamentos recorrentes', 'agendamentos_recorrentes', 'criar', 1),
('agendamentos_recorrentes.editar', 'Editar agendamentos recorrentes', 'agendamentos_recorrentes', 'editar', 1),
('agendamentos_recorrentes.excluir', 'Excluir agendamentos recorrentes', 'agendamentos_recorrentes', 'excluir', 1),
('clientes.visualizar', 'Visualizar clientes', 'clientes', 'visualizar', 1),
('clientes.criar', 'Criar clientes', 'clientes', 'criar', 1),
('clientes.editar', 'Editar clientes', 'clientes', 'editar', 1),
('clientes.excluir', 'Excluir clientes', 'clientes', 'excluir', 1),
('pets.visualizar', 'Visualizar pets', 'pets', 'visualizar', 1),
('pets.criar', 'Criar pets', 'pets', 'criar', 1),
('pets.editar', 'Editar pets', 'pets', 'editar', 1),
('pets.excluir', 'Excluir pets', 'pets', 'excluir', 1),
('usuarios.visualizar', 'Visualizar usuários', 'usuarios', 'visualizar', 1),
('usuarios.criar', 'Criar usuários', 'usuarios', 'criar', 1),
('usuarios.editar', 'Editar usuários', 'usuarios', 'editar', 1),
('usuarios.excluir', 'Excluir usuários', 'usuarios', 'excluir', 1),
('admin.permissoes', 'Gerenciar permissões', 'admin', 'permissoes', 1),
('admin.niveis', 'Gerenciar níveis de acesso', 'admin', 'niveis', 1),
('relatorios.visualizar', 'Visualizar relatórios', 'relatorios', 'visualizar', 1),
('configuracoes.visualizar', 'Visualizar configurações', 'configuracoes', 'visualizar', 1),
('configuracoes.editar', 'Editar configurações', 'configuracoes', 'editar', 1);

-- Inserir configurações padrão do sistema
INSERT IGNORE INTO `configuracoes` (`chave`, `valor`, `descricao`, `tipo`) VALUES 
('sistema.nome', 'Bichos do Bairro', 'Nome do sistema', 'string'),
('sistema.versao', '1.0.0', 'Versão do sistema', 'string'),
('sistema.timezone', 'America/Sao_Paulo', 'Fuso horário do sistema', 'string'),
('agendamento.horario_inicio', '08:00', 'Horário de início dos agendamentos', 'time'),
('agendamento.horario_fim', '18:00', 'Horário de fim dos agendamentos', 'time'),
('agendamento.duracao_padrao', '60', 'Duração padrão dos agendamentos (minutos)', 'integer'),
('agendamento.intervalo', '30', 'Intervalo entre agendamentos (minutos)', 'integer'),
('notificacao.email_ativo', '1', 'Ativar notificações por email', 'boolean'),
('notificacao.sms_ativo', '0', 'Ativar notificações por SMS', 'boolean'),
('backup.automatico', '1', 'Ativar backup automático', 'boolean'),
('backup.frequencia', 'diario', 'Frequência do backup (diario, semanal, mensal)', 'string');

-- =====================================================
-- ATUALIZAÇÕES E CORREÇÕES
-- =====================================================

-- Adicionar campo recorrencia_id na tabela agendamentos se não existir
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'bichosdobairro' 
     AND TABLE_NAME = 'agendamentos' 
     AND COLUMN_NAME = 'recorrencia_id') = 0,
    'ALTER TABLE agendamentos ADD COLUMN recorrencia_id int(11) DEFAULT NULL AFTER observacoes',
    'SELECT "Campo recorrencia_id já existe" as resultado'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar campo valor na tabela agendamentos se não existir
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'bichosdobairro' 
     AND TABLE_NAME = 'agendamentos' 
     AND COLUMN_NAME = 'valor') = 0,
    'ALTER TABLE agendamentos ADD COLUMN valor decimal(10,2) DEFAULT NULL AFTER recorrencia_id',
    'SELECT "Campo valor já existe" as resultado'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar campo peso na tabela pets se não existir
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'bichosdobairro' 
     AND TABLE_NAME = 'pets' 
     AND COLUMN_NAME = 'peso') = 0,
    'ALTER TABLE pets ADD COLUMN peso decimal(5,2) DEFAULT NULL AFTER idade',
    'SELECT "Campo peso já existe" as resultado'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar campo observacoes na tabela pets se não existir
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'bichosdobairro' 
     AND TABLE_NAME = 'pets' 
     AND COLUMN_NAME = 'observacoes') = 0,
    'ALTER TABLE pets ADD COLUMN observacoes text AFTER peso',
    'SELECT "Campo observacoes já existe" as resultado'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar campo observacoes na tabela clientes se não existir
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'bichosdobairro' 
     AND TABLE_NAME = 'clientes' 
     AND COLUMN_NAME = 'observacoes') = 0,
    'ALTER TABLE clientes ADD COLUMN observacoes text AFTER endereco',
    'SELECT "Campo observacoes já existe" as resultado'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar campo atualizado_em na tabela clientes se não existir
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'bichosdobairro' 
     AND TABLE_NAME = 'clientes' 
     AND COLUMN_NAME = 'atualizado_em') = 0,
    'ALTER TABLE clientes ADD COLUMN atualizado_em timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER criado_em',
    'SELECT "Campo atualizado_em já existe" as resultado'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar campo atualizado_em na tabela pets se não existir
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'bichosdobairro' 
     AND TABLE_NAME = 'pets' 
     AND COLUMN_NAME = 'atualizado_em') = 0,
    'ALTER TABLE pets ADD COLUMN atualizado_em timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER criado_em',
    'SELECT "Campo atualizado_em já existe" as resultado'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar campo valor na tabela agendamentos_recorrentes se não existir
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'bichosdobairro' 
     AND TABLE_NAME = 'agendamentos_recorrentes' 
     AND COLUMN_NAME = 'valor') = 0,
    'ALTER TABLE agendamentos_recorrentes ADD COLUMN valor decimal(10,2) DEFAULT NULL AFTER observacoes',
    'SELECT "Campo valor já existe" as resultado'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- VERIFICAÇÃO FINAL
-- =====================================================

-- Mostrar todas as tabelas criadas
SELECT 'TABELAS CRIADAS:' as info;
SHOW TABLES;

-- Mostrar usuário administrador
SELECT 'USUÁRIO ADMINISTRADOR:' as info;
SELECT id, nome, email, nivel_acesso, ativo FROM usuarios WHERE nivel_acesso = 'admin';

-- Mostrar estatísticas
SELECT 'ESTATÍSTICAS:' as info;
SELECT 
    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
    (SELECT COUNT(*) FROM clientes) as total_clientes,
    (SELECT COUNT(*) FROM pets) as total_pets,
    (SELECT COUNT(*) FROM agendamentos) as total_agendamentos,
    (SELECT COUNT(*) FROM agendamentos_recorrentes) as total_agendamentos_recorrentes,
    (SELECT COUNT(*) FROM permissoes) as total_permissoes,
    (SELECT COUNT(*) FROM niveis_acesso) as total_niveis;

-- Finalizar transação
COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- MENSAGEM DE SUCESSO
-- =====================================================
SELECT 'SINCRONIZAÇÃO CONCLUÍDA COM SUCESSO!' as resultado;
SELECT 'Todas as tabelas foram criadas e os dados padrão foram inseridos.' as status;
SELECT 'O sistema está pronto para uso.' as finalizacao; 