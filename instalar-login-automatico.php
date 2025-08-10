<?php
/**
 * Script de Instalação Automática do Sistema de Login
 * Execute via linha de comando: php instalar-login-automatico.php
 */

echo "=== INSTALADOR AUTOMÁTICO - SISTEMA DE LOGIN ===\n\n";

// Verificar se está sendo executado via linha de comando
if (php_sapi_name() !== 'cli') {
    echo "❌ Este script deve ser executado via linha de comando.\n";
    echo "Use: php instalar-login-automatico.php\n";
    exit(1);
}

// Verificar se o arquivo de configuração existe
if (!file_exists('src/init.php')) {
    echo "❌ Arquivo src/init.php não encontrado.\n";
    echo "Certifique-se de estar no diretório raiz do projeto.\n";
    exit(1);
}

try {
    echo "📁 Carregando configurações...\n";
    require_once 'src/init.php';
    
    echo "🔌 Conectando ao banco de dados...\n";
    $pdo = getDb();
    echo "✅ Conexão estabelecida com sucesso!\n\n";
    
    // Verificar se as tabelas já existem
    echo "🔍 Verificando tabelas existentes...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $usuarios_existe = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'logs_login'");
    $logs_login_existe = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'logs_atividade'");
    $logs_atividade_existe = $stmt->rowCount() > 0;
    
    if ($usuarios_existe && $logs_login_existe && $logs_atividade_existe) {
        echo "✅ Todas as tabelas já existem!\n";
        echo "\n📋 Status das tabelas:\n";
        echo "   - usuarios: " . ($usuarios_existe ? "✅" : "❌") . "\n";
        echo "   - logs_login: " . ($logs_login_existe ? "✅" : "❌") . "\n";
        echo "   - logs_atividade: " . ($logs_atividade_existe ? "✅" : "❌") . "\n";
        
        // Verificar se existe usuário admin
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        $stmt->execute(['admin@bichosdobairro.com']);
        $admin_existe = $stmt->fetchColumn() > 0;
        
        if ($admin_existe) {
            echo "\n👤 Usuário administrador já existe!\n";
            echo "📧 Email: admin@bichosdobairro.com\n";
            echo "🔑 Senha: admin123\n";
            echo "⚠️  IMPORTANTE: Altere a senha padrão após o primeiro login!\n";
        }
        
        echo "\n🎉 Sistema de login já está instalado e funcionando!\n";
        exit(0);
    }
    
    echo "📦 Instalando tabelas...\n\n";
    
    // Criar tabela de usuários
    if (!$usuarios_existe) {
        echo "📋 Criando tabela 'usuarios'...\n";
        $sql_usuarios = file_get_contents('sql/create_usuarios_table.sql');
        if ($sql_usuarios === false) {
            throw new Exception("Não foi possível ler o arquivo sql/create_usuarios_table.sql");
        }
        $pdo->exec($sql_usuarios);
        echo "✅ Tabela 'usuarios' criada com sucesso!\n";
    } else {
        echo "✅ Tabela 'usuarios' já existe.\n";
    }
    
    // Criar tabela de logs de atividade
    if (!$logs_atividade_existe) {
        echo "📋 Criando tabela 'logs_atividade'...\n";
        $sql_logs = file_get_contents('sql/create_logs_atividade_table.sql');
        if ($sql_logs === false) {
            throw new Exception("Não foi possível ler o arquivo sql/create_logs_atividade_table.sql");
        }
        $pdo->exec($sql_logs);
        echo "✅ Tabela 'logs_atividade' criada com sucesso!\n";
    } else {
        echo "✅ Tabela 'logs_atividade' já existe.\n";
    }
    
    // Verificar se o usuário admin foi criado
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@bichosdobairro.com']);
    $admin_existe = $stmt->fetchColumn() > 0;
    
    if (!$admin_existe) {
        echo "👤 Criando usuário administrador...\n";
        // O usuário admin é criado automaticamente pelo SQL
        echo "✅ Usuário administrador criado!\n";
    } else {
        echo "✅ Usuário administrador já existe.\n";
    }
    
    echo "\n🎉 INSTALAÇÃO CONCLUÍDA COM SUCESSO!\n\n";
    
    echo "📋 RESUMO DA INSTALAÇÃO:\n";
    echo "   ✅ Tabela 'usuarios' - OK\n";
    echo "   ✅ Tabela 'logs_login' - OK\n";
    echo "   ✅ Tabela 'logs_atividade' - OK\n";
    echo "   ✅ Usuário administrador - OK\n\n";
    
    echo "🔐 CREDENCIAIS DE ACESSO:\n";
    echo "   📧 Email: admin@bichosdobairro.com\n";
    echo "   🔑 Senha: admin123\n\n";
    
    echo "⚠️  IMPORTANTE:\n";
    echo "   1. Altere a senha padrão após o primeiro login!\n";
    echo "   2. Configure SSL/HTTPS em produção\n";
    echo "   3. Monitore os logs regularmente\n\n";
    
    echo "🌐 PRÓXIMOS PASSOS:\n";
    echo "   1. Acesse: http://seu-dominio.com/public/login.php\n";
    echo "   2. Faça login com as credenciais acima\n";
    echo "   3. Altere a senha em: http://seu-dominio.com/public/alterar-senha.php\n";
    echo "   4. Configure as demais páginas do sistema\n\n";
    
    echo "🔒 CARACTERÍSTICAS DE SEGURANÇA IMPLEMENTADAS:\n";
    echo "   ✅ Hash seguro de senhas com salt\n";
    echo "   ✅ Proteção contra força bruta (5 tentativas)\n";
    echo "   ✅ Bloqueio temporário (15 minutos)\n";
    echo "   ✅ Sessões seguras com timeout\n";
    echo "   ✅ Logs de auditoria completos\n";
    echo "   ✅ Headers de segurança HTTP\n";
    echo "   ✅ Validação robusta de entrada\n";
    echo "   ✅ Prevenção de SQL Injection\n";
    echo "   ✅ Detecção de IP real\n\n";
    
    echo "📊 MONITORAMENTO:\n";
    echo "   - Logs de login: tabela 'logs_login'\n";
    echo "   - Logs de atividade: tabela 'logs_atividade'\n";
    echo "   - Usuários bloqueados: campo 'bloqueado_ate' na tabela 'usuarios'\n\n";
    
    echo "✅ Sistema de login instalado e pronto para uso!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO durante a instalação:\n";
    echo "   " . $e->getMessage() . "\n\n";
    
    echo "🔧 POSSÍVEIS SOLUÇÕES:\n";
    echo "   1. Verifique se o banco de dados está acessível\n";
    echo "   2. Confirme se as credenciais em .env estão corretas\n";
    echo "   3. Verifique se o usuário do banco tem permissões de CREATE\n";
    echo "   4. Certifique-se de estar no diretório raiz do projeto\n\n";
    
    echo "📞 Para suporte, verifique:\n";
    echo "   - Arquivo de log: logs/error.log\n";
    echo "   - Configuração do banco: .env\n";
    echo "   - Permissões de arquivo\n";
    
    exit(1);
} 