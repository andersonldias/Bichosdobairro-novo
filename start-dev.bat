@echo off
REM ========================================
REM SCRIPT DE INICIALIZAÇÃO - DESENVOLVIMENTO
REM Sistema Bichos do Bairro
REM ========================================

echo.
echo ========================================
echo    SISTEMA BICHOS DO BAIRRO
echo    Script de Inicializacao
echo ========================================
echo.

REM Verificar se PHP está instalado
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERRO] PHP nao encontrado no PATH
    echo Por favor, instale o PHP e adicione ao PATH
    pause
    exit /b 1
)

REM Verificar se Composer está instalado
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERRO] Composer nao encontrado no PATH
    echo Por favor, instale o Composer e adicione ao PATH
    pause
    exit /b 1
)

echo [INFO] Verificando dependencias...

REM Verificar se vendor existe
if not exist "vendor" (
    echo [INFO] Instalando dependencias do Composer...
    composer install
    if %errorlevel% neq 0 (
        echo [ERRO] Falha ao instalar dependencias
        pause
        exit /b 1
    )
)

REM Verificar se arquivo .env existe
if not exist ".env" (
    echo [INFO] Criando arquivo .env...
    if exist "env.example" (
        copy "env.example" ".env"
        echo [INFO] Arquivo .env criado. Configure as variaveis conforme necessario.
    ) else (
        echo [ERRO] Arquivo env.example nao encontrado
        pause
        exit /b 1
    )
)

REM Criar diretórios necessários
echo [INFO] Criando diretorios necessarios...
if not exist "logs" mkdir logs
if not exist "backups" mkdir backups
if not exist "cache" mkdir cache
if not exist "uploads" mkdir uploads

REM Verificar conexão com banco
echo [INFO] Testando conexao com banco de dados...
php -r "require 'src/init.php'; echo 'Conexao OK';" 2>nul
if %errorlevel% neq 0 (
    echo [AVISO] Erro na conexao com banco. Verifique as configuracoes no .env
)

REM Iniciar servidor de desenvolvimento
echo.
echo ========================================
echo    INICIANDO SERVIDOR DE DESENVOLVIMENTO
echo ========================================
echo.
echo [INFO] Servidor iniciado em: http://localhost:8000
echo [INFO] Pressione Ctrl+C para parar o servidor
echo.

REM Iniciar servidor PHP
php -S localhost:8000 -t public

pause 