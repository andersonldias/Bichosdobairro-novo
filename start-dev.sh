#!/bin/bash

# ========================================
# SCRIPT DE INICIALIZAÇÃO - DESENVOLVIMENTO
# Sistema Bichos do Bairro
# ========================================

echo ""
echo "========================================"
echo "    SISTEMA BICHOS DO BAIRRO"
echo "    Script de Inicialização"
echo "========================================"
echo ""

# Verificar se PHP está instalado
if ! command -v php &> /dev/null; then
    echo "[ERRO] PHP não encontrado no PATH"
    echo "Por favor, instale o PHP e adicione ao PATH"
    exit 1
fi

# Verificar se Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "[ERRO] Composer não encontrado no PATH"
    echo "Por favor, instale o Composer e adicione ao PATH"
    exit 1
fi

echo "[INFO] Verificando dependências..."

# Verificar se vendor existe
if [ ! -d "vendor" ]; then
    echo "[INFO] Instalando dependências do Composer..."
    composer install
    if [ $? -ne 0 ]; then
        echo "[ERRO] Falha ao instalar dependências"
        exit 1
    fi
fi

# Verificar se arquivo .env existe
if [ ! -f ".env" ]; then
    echo "[INFO] Criando arquivo .env..."
    if [ -f "env.example" ]; then
        cp env.example .env
        echo "[INFO] Arquivo .env criado. Configure as variáveis conforme necessário."
    else
        echo "[ERRO] Arquivo env.example não encontrado"
        exit 1
    fi
fi

# Criar diretórios necessários
echo "[INFO] Criando diretórios necessários..."
mkdir -p logs backups cache uploads

# Definir permissões
chmod 755 logs backups cache uploads
chmod 600 .env

# Verificar conexão com banco
echo "[INFO] Testando conexão com banco de dados..."
php -r "require 'src/init.php'; echo 'Conexão OK';" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "[AVISO] Erro na conexão com banco. Verifique as configurações no .env"
fi

# Verificar se há processos PHP rodando na porta 8000
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null ; then
    echo "[AVISO] Porta 8000 já está em uso. Tentando encontrar processo..."
    PID=$(lsof -ti:8000)
    echo "[INFO] Processo encontrado: $PID"
    read -p "Deseja encerrar o processo? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        kill -9 $PID
        echo "[INFO] Processo encerrado."
    else
        echo "[INFO] Usando porta 8001..."
        PORT=8001
    fi
else
    PORT=8000
fi

# Iniciar servidor de desenvolvimento
echo ""
echo "========================================"
echo "    INICIANDO SERVIDOR DE DESENVOLVIMENTO"
echo "========================================"
echo ""
echo "[INFO] Servidor iniciado em: http://localhost:$PORT"
echo "[INFO] Pressione Ctrl+C para parar o servidor"
echo ""

# Função para limpar ao sair
cleanup() {
    echo ""
    echo "[INFO] Encerrando servidor..."
    exit 0
}

# Capturar Ctrl+C
trap cleanup SIGINT

# Iniciar servidor PHP
php -S localhost:$PORT -t public 