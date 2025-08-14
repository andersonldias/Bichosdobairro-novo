@echo off
echo ========================================
echo MIGRACAO COMPLETA PARA REPOSITORIO GITHUB
echo Commit: f843394c6822f243c8aa4a74acdcf5db4785bdb5
echo ========================================
echo.

cd /d "c:\bichosdobairro-php"

echo 1. Verificando se repositorio existe...
if not exist "Bichosdobairro-novo" (
    echo ❌ ERRO: Pasta Bichosdobairro-novo nao encontrada!
    echo Execute primeiro o comando de download do commit.
    pause
    exit /b 1
)
echo ✅ Repositorio encontrado!
echo.

echo 2. Criando backup de seguranca...
set BACKUP_DIR=backup-projeto-original-%date:~-4,4%-%date:~-10,2%-%date:~-7,2%-%time:~0,2%-%time:~3,2%
mkdir "%BACKUP_DIR%" 2>nul
if exist "src" xcopy "src" "%BACKUP_DIR%\src\" /E /I /Q
if exist "public" xcopy "public" "%BACKUP_DIR%\public\" /E /I /Q
if exist "sql" xcopy "sql" "%BACKUP_DIR%\sql\" /E /I /Q
if exist "scripts" xcopy "scripts" "%BACKUP_DIR%\scripts\" /E /I /Q
echo ✅ Backup criado em: %BACKUP_DIR%
echo.

echo 3. Removendo projeto atual...
if exist "src" rmdir /s /q "src" 2>nul
if exist "public" rmdir /s /q "public" 2>nul
if exist "sql" rmdir /s /q "sql" 2>nul
if exist "scripts" rmdir /s /q "scripts" 2>nul
echo ✅ Projeto atual removido!
echo.

echo 4. Movendo repositorio clonado...
if exist "Bichosdobairro-novo\src" (
    move "Bichosdobairro-novo\src" "src"
    echo ✅ Pasta src movida!
) else (
    echo ⚠️  Pasta src nao encontrada no repositorio
)

if exist "Bichosdobairro-novo\public" (
    move "Bichosdobairro-novo\public" "public"
    echo ✅ Pasta public movida!
) else (
    echo ⚠️  Pasta public nao encontrada no repositorio
)

if exist "Bichosdobairro-novo\sql" (
    move "Bichosdobairro-novo\sql" "sql"
    echo ✅ Pasta sql movida!
) else (
    echo ⚠️  Pasta sql nao encontrada no repositorio
)

if exist "Bichosdobairro-novo\scripts" (
    move "Bichosdobairro-novo\scripts" "scripts"
    echo ✅ Pasta scripts movida!
) else (
    echo ⚠️  Pasta scripts nao encontrada no repositorio
)
echo.

echo 5. Copiando arquivos de configuracao...
if exist "Bichosdobairro-novo\composer.json" (
    copy "Bichosdobairro-novo\composer.json" "composer.json" /Y
    echo ✅ composer.json copiado!
)

if exist "Bichosdobairro-novo\.htaccess" (
    copy "Bichosdobairro-novo\.htaccess" ".htaccess" /Y
    echo ✅ .htaccess copiado!
)

if exist "Bichosdobairro-novo\config-producao.env" (
    copy "Bichosdobairro-novo\config-producao.env" ".env" /Y
    echo ✅ .env criado!
) else if exist "Bichosdobairro-novo\env.production" (
    copy "Bichosdobairro-novo\env.production" ".env" /Y
    echo ✅ .env criado a partir de env.production!
)

if exist "Bichosdobairro-novo\README.md" (
    copy "Bichosdobairro-novo\README.md" "README.md" /Y
    echo ✅ README.md atualizado!
)
echo.

echo 6. Copiando documentacao...
for %%f in ("Bichosdobairro-novo\*.md") do (
    copy "%%f" ".\" /Y >nul 2>&1
)
echo ✅ Documentacao copiada!
echo.

echo 7. Limpando pasta temporaria...
rmdir /s /q "Bichosdobairro-novo" 2>nul
echo ✅ Limpeza concluida!
echo.

echo 8. Verificando estrutura final...
if exist "src" echo ✅ Pasta src: OK
if exist "public" echo ✅ Pasta public: OK
if exist "sql" echo ✅ Pasta sql: OK
if exist "scripts" echo ✅ Pasta scripts: OK
if exist "composer.json" echo ✅ composer.json: OK
if exist ".env" echo ✅ .env: OK
echo.

echo ========================================
echo ✅ MIGRACAO CONCLUIDA COM SUCESSO!
echo ========================================
echo.
echo Backup salvo em: %BACKUP_DIR%
echo.
echo Proximos passos:
echo 1. Execute: composer install
echo 2. Configure o arquivo .env conforme necessario
echo 3. Teste o sistema: php -S localhost:8000 -t public
echo 4. Acesse: http://localhost:8000
echo.
echo Commit migrado: f843394c6822f243c8aa4a74acdcf5db4785bdb5
echo.
pause