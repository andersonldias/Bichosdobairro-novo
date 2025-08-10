-- ========================================
-- SCRIPT PARA LIMPAR BANCO DE DADOS
-- Sistema Bichos do Bairro
-- ATENÇÃO: Este script APAGA TODOS os dados!
-- ========================================

-- Desabilitar verificação de foreign keys para evitar erros
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================
-- APAGAR TODAS AS TABELAS
-- ========================================

-- Tabelas de agendamentos
DROP TABLE IF EXISTS `agendamentos_recorrentes_ocorrencias`;
DROP TABLE IF EXISTS `agendamentos_recorrentes`;
DROP TABLE IF EXISTS `agendamentos`;

-- Tabelas de logs
DROP TABLE IF EXISTS `logs_agendamentos_recorrentes`;
DROP TABLE IF EXISTS `logs_atividade`;
DROP TABLE IF EXISTS `logs_login`;

-- Tabelas de usuários e permissões
DROP TABLE IF EXISTS `usuarios_permissoes`;
DROP TABLE IF EXISTS `nivel_permissoes`;
DROP TABLE IF EXISTS `permissoes`;
DROP TABLE IF EXISTS `niveis_acesso`;
DROP TABLE IF EXISTS `usuarios`;

-- Tabelas de clientes e pets
DROP TABLE IF EXISTS `telefones`;
DROP TABLE IF EXISTS `pets`;
DROP TABLE IF EXISTS `clientes`;

-- Tabelas do sistema
DROP TABLE IF EXISTS `notificacoes`;
DROP TABLE IF EXISTS `configuracoes`;

-- Reabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- VERIFICAÇÃO
-- ========================================

-- Verificar se as tabelas foram apagadas
SHOW TABLES;

-- ========================================
-- MENSAGEM DE CONFIRMAÇÃO
-- ========================================

SELECT 'Banco de dados limpo com sucesso!' as status;
SELECT 'Agora você pode importar o backup completo.' as proximo_passo; 