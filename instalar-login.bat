@echo off
chcp 65001 >nul
title Instalador Sistema de Login - Bichos do Bairro

echo.
echo ========================================
echo   INSTALADOR SISTEMA DE LOGIN
echo   Bichos do Bairro
echo ========================================
echo.

echo Verificando se o PHP está instalado...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ❌ ERRO: PHP não encontrado!
    echo.
    echo Para instalar o PHP:
    echo 1. Baixe o PHP em: https://windows.php.net/download/
    echo 2. Extraia para C:\php
    echo 3. Adicione C:\php ao PATH do sistema
    echo 4. Execute este script novamente
    echo.
    pause
    exit /b 1
)

echo ✅ PHP encontrado!
echo.

echo Verificando arquivos necessários...
if not exist "src\init.php" (
    echo ❌ ERRO: Arquivo src\init.php não encontrado!
    echo Certifique-se de estar no diretório raiz do projeto.
    echo.
    pause
    exit /b 1
)

if not exist "sql\create_usuarios_table.sql" (
    echo ❌ ERRO: Arquivo sql\create_usuarios_table.sql não encontrado!
    echo.
    pause
    exit /b 1
)

if not exist "sql\create_logs_atividade_table.sql" (
    echo ❌ ERRO: Arquivo sql\create_logs_atividade_table.sql não encontrado!
    echo.
    pause
    exit /b 1
)

echo ✅ Todos os arquivos necessários encontrados!
echo.

echo Iniciando instalação automática...
echo.

php instalar-login-automatico.php

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo   INSTALAÇÃO CONCLUÍDA COM SUCESSO!
    echo ========================================
    echo.
    echo 🌐 Para acessar o sistema:
    echo    http://localhost/public/login.php
    echo.
    echo 🔐 Credenciais padrão:
    echo    Email: admin@bichosdobairro.com
    echo    Senha: admin123
    echo.
    echo ⚠️  IMPORTANTE: Altere a senha após o primeiro login!
    echo.
) else (
    echo.
    echo ========================================
    echo   ERRO NA INSTALAÇÃO
    echo ========================================
    echo.
    echo Verifique os erros acima e tente novamente.
    echo.
)

echo Pressione qualquer tecla para sair...
pause >nul 