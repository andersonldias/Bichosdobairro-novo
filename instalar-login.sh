#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "========================================"
echo "   INSTALADOR SISTEMA DE LOGIN"
echo "   Bichos do Bairro"
echo "========================================"
echo -e "${NC}"

# Verificar se o PHP está instalado
echo "Verificando se o PHP está instalado..."
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ ERRO: PHP não encontrado!${NC}"
    echo ""
    echo "Para instalar o PHP:"
    echo "Ubuntu/Debian: sudo apt-get install php php-mysql"
    echo "CentOS/RHEL: sudo yum install php php-mysql"
    echo "macOS: brew install php"
    echo ""
    exit 1
fi

echo -e "${GREEN}✅ PHP encontrado!${NC}"
echo ""

# Verificar arquivos necessários
echo "Verificando arquivos necessários..."
if [ ! -f "src/init.php" ]; then
    echo -e "${RED}❌ ERRO: Arquivo src/init.php não encontrado!${NC}"
    echo "Certifique-se de estar no diretório raiz do projeto."
    echo ""
    exit 1
fi

if [ ! -f "sql/create_usuarios_table.sql" ]; then
    echo -e "${RED}❌ ERRO: Arquivo sql/create_usuarios_table.sql não encontrado!${NC}"
    echo ""
    exit 1
fi

if [ ! -f "sql/create_logs_atividade_table.sql" ]; then
    echo -e "${RED}❌ ERRO: Arquivo sql/create_logs_atividade_table.sql não encontrado!${NC}"
    echo ""
    exit 1
fi

echo -e "${GREEN}✅ Todos os arquivos necessários encontrados!${NC}"
echo ""

# Verificar permissões
echo "Verificando permissões..."
if [ ! -r "src/init.php" ]; then
    echo -e "${YELLOW}⚠️  AVISO: Arquivo src/init.php não tem permissão de leitura${NC}"
    echo "Tentando corrigir permissões..."
    chmod 644 src/init.php
fi

if [ ! -r "sql/create_usuarios_table.sql" ]; then
    echo -e "${YELLOW}⚠️  AVISO: Arquivo sql/create_usuarios_table.sql não tem permissão de leitura${NC}"
    echo "Tentando corrigir permissões..."
    chmod 644 sql/create_usuarios_table.sql
fi

if [ ! -r "sql/create_logs_atividade_table.sql" ]; then
    echo -e "${YELLOW}⚠️  AVISO: Arquivo sql/create_logs_atividade_table.sql não tem permissão de leitura${NC}"
    echo "Tentando corrigir permissões..."
    chmod 644 sql/create_logs_atividade_table.sql
fi

echo -e "${GREEN}✅ Permissões verificadas!${NC}"
echo ""

echo "Iniciando instalação automática..."
echo ""

# Executar o script PHP
php instalar-login-automatico.php

# Verificar se a instalação foi bem-sucedida
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}========================================"
    echo "   INSTALAÇÃO CONCLUÍDA COM SUCESSO!"
    echo "========================================"
    echo -e "${NC}"
    echo ""
    echo -e "${BLUE}🌐 Para acessar o sistema:${NC}"
    echo "   http://localhost/public/login.php"
    echo ""
    echo -e "${BLUE}🔐 Credenciais padrão:${NC}"
    echo "   Email: admin@bichosdobairro.com"
    echo "   Senha: admin123"
    echo ""
    echo -e "${YELLOW}⚠️  IMPORTANTE: Altere a senha após o primeiro login!${NC}"
    echo ""
    
    # Perguntar se quer abrir o navegador
    read -p "Deseja abrir o navegador automaticamente? (y/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        if command -v xdg-open &> /dev/null; then
            xdg-open "http://localhost/public/login.php" 2>/dev/null
        elif command -v open &> /dev/null; then
            open "http://localhost/public/login.php" 2>/dev/null
        else
            echo "Navegador não pode ser aberto automaticamente."
        fi
    fi
else
    echo ""
    echo -e "${RED}========================================"
    echo "   ERRO NA INSTALAÇÃO"
    echo "========================================"
    echo -e "${NC}"
    echo ""
    echo "Verifique os erros acima e tente novamente."
    echo ""
fi

echo "Pressione Enter para sair..."
read 