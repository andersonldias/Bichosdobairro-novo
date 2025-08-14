@echo off
echo ========================================
echo MIGRACAO FINAL - REPOSITORIO GITHUB
echo Commit: f843394c6822f243c8aa4a74acdcf5db4785bdb5
echo ========================================
echo.

cd /d "c:\bichosdobairro-php"

echo 1. Verificando repositorio...
if not exist "Bichosdobairro-novo" (
    echo ❌ ERRO: Execute primeiro corrigir-repositorio.bat
    pause
    exit /b 1
)
echo ✅ Repositorio encontrado!
echo.

echo 2. Removendo backup antigo...
rmdir /s /q "backup-projeto-original-2025-13-08" 2>nul
echo ✅ Backup antigo removido!
echo.

echo 3. Criando novo backup...
set BACKUP_DIR=backup-original-%date:~-4,4%-%date:~-10,2%-%date:~-7,2%
mkdir "%BACKUP_DIR%" 2>nul
if exist "src" xcopy "src" "%BACKUP_DIR%\src\" /E /I /Q
if exist "public" xcopy "public" "%BACKUP_DIR%\public\" /E /I /Q
if exist "sql" xcopy "sql" "%BACKUP_DIR%\sql\" /E /I /Q
echo ✅ Backup criado: %BACKUP_DIR%
echo.

echo 4. Removendo estrutura atual...
rmdir /s /q "src" 2>nul
rmdir /s /q "public" 2>nul
rmdir /s /q "sql" 2>nul
rmdir /s /q "scripts" 2>nul
echo ✅ Estrutura atual removida!
echo.

echo 5. Copiando nova estrutura...
xcopy "Bichosdobairro-novo\src" "src\" /E /I /Q
xcopy "Bichosdobairro-novo\public" "public\" /E /I /Q
xcopy "Bichosdobairro-novo\sql" "sql\" /E /I /Q
if exist "Bichosdobairro-novo\scripts" xcopy "Bichosdobairro-novo\scripts" "scripts\" /E /I /Q
echo ✅ Nova estrutura copiada!
echo.

echo 6. Copiando arquivos de configuracao...
copy "Bichosdobairro-novo\composer.json" "composer.json" /Y
copy "Bichosdobairro-novo\.htaccess" ".htaccess" /Y
if exist "Bichosdobairro-novo\env.production" copy "Bichosdobairro-novo\env.production" ".env" /Y
echo ✅ Configuracoes copiadas!
echo.

echo 7. Limpando repositorio temporario...
rmdir /s /q "Bichosdobairro-novo" 2>nul
echo ✅ Limpeza concluida!
echo.

echo 8. Verificacao final...
if exist "src" echo ✅ src: OK
if exist "public" echo ✅ public: OK
if exist "sql" echo ✅ sql: OK
if exist "composer.json" echo ✅ composer.json: OK
echo.

echo ========================================
echo 🎉 MIGRACAO CONCLUIDA COM SUCESSO!
echo ========================================
echo.
echo Backup: %BACKUP_DIR%
echo Commit: f843394c6822f243c8aa4a74acdcf5db4785bdb5
echo.
echo Proximos passos:
echo 1. composer install
echo 2. Configurar .env
echo 3. Testar sistema
echo.
pause