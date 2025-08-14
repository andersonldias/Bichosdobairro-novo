@echo off
echo ========================================
echo MIGRACAO COMPLETA PARA REPOSITORIO GITHUB
echo ========================================
echo.

cd /d "c:\bichosdobairro-php"

echo 1. Criando backup de seguranca...
mkdir backup-projeto-original-%date:~-4,4%-%date:~-10,2%-%date:~-7,2% 2>nul
xcopy src backup-projeto-original-%date:~-4,4%-%date:~-10,2%-%date:~-7,2%\src\ /E /I /Q
xcopy public backup-projeto-original-%date:~-4,4%-%date:~-10,2%-%date:~-7,2%\public\ /E /I /Q
xcopy sql backup-projeto-original-%date:~-4,4%-%date:~-10,2%-%date:~-7,2%\sql\ /E /I /Q
echo ✅ Backup criado!
echo.

echo 2. Removendo projeto atual...
rmdir /s /q src 2>nul
rmdir /s /q public 2>nul
rmdir /s /q sql 2>nul
rmdir /s /q scripts 2>nul
echo ✅ Projeto atual removido!
echo.

echo 3. Movendo repositorio clonado...
move "Bichosdobairro-novo\src" "src"
move "Bichosdobairro-novo\public" "public"
move "Bichosdobairro-novo\sql" "sql"
move "Bichosdobairro-novo\scripts" "scripts"
echo ✅ Estrutura principal movida!
echo.

echo 4. Copiando arquivos de configuracao...
copy "Bichosdobairro-novo\composer.json" "composer.json" /Y
copy "Bichosdobairro-novo\.htaccess" ".htaccess" /Y
copy "Bichosdobairro-novo\config-producao.env" ".env" /Y
echo ✅ Configuracoes copiadas!
echo.

echo 5. Limpando pasta temporaria...
rmdir /s /q "Bichosdobairro-novo" 2>nul
echo ✅ Limpeza concluida!
echo.

echo ========================================
echo ✅ MIGRACAO CONCLUIDA COM SUCESSO!
echo ========================================
echo.
echo Proximos passos:
echo 1. Execute: composer install
echo 2. Configure o arquivo .env
echo 3. Teste o sistema
echo.
pause