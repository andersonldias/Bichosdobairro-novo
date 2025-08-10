-- ========================================
-- SCRIPT SEGURO PARA LIMPAR BANCO DE DADOS
-- Sistema Bichos do Bairro
-- ATENÇÃO: Este script APAGA TODOS os dados!
-- ========================================

-- ========================================
-- PASSO 1: DESABILITAR VERIFICAÇÃO DE FOREIGN KEYS
-- ========================================
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================
-- PASSO 2: APAGAR TABELAS NA ORDEM CORRETA
-- ========================================

-- Primeiro as tabelas que dependem de outras (filhas)
DROP TABLE IF EXISTS `agendamentos_recorrentes_ocorrencias`;
DROP TABLE IF EXISTS `agendamentos`;
DROP TABLE IF EXISTS `agendamentos_recorrentes`;
DROP TABLE IF EXISTS `logs_agendamentos_recorrentes`;
DROP TABLE IF EXISTS `logs_atividade`;
DROP TABLE IF EXISTS `logs_login`;
DROP TABLE IF EXISTS `usuarios_permissoes`;
DROP TABLE IF EXISTS `nivel_permissoes`;
DROP TABLE IF EXISTS `telefones`;
DROP TABLE IF EXISTS `pets`;
DROP TABLE IF EXISTS `clientes`;

-- Depois as tabelas principais (pais)
DROP TABLE IF EXISTS `permissoes`;
DROP TABLE IF EXISTS `niveis_acesso`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `notificacoes`;
DROP TABLE IF EXISTS `configuracoes`;

-- ========================================
-- PASSO 3: REABILITAR VERIFICAÇÃO DE FOREIGN KEYS
-- ========================================
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- PASSO 4: VERIFICAÇÃO
-- ========================================

-- Verificar se as tabelas foram apagadas
SHOW TABLES;

-- ========================================
-- MENSAGEM DE CONFIRMAÇÃO
-- ========================================

SELECT 'Banco de dados limpo com sucesso!' as status;
SELECT 'Agora você pode importar o backup completo.' as proximo_passo; 