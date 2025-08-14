@echo off
echo ========================================
echo CORRIGINDO PROBLEMA DE PERMISSAO
echo ========================================
echo.

cd /d "c:\bichosdobairro-php"

echo 1. Removendo pasta temp-repo com forcas...
rmdir /s /q "temp-repo" 2>nul
echo ✅ temp-repo removida!
echo.

echo 2. Removendo Bichosdobairro-novo se existir...
rmdir /s /q "Bichosdobairro-novo" 2>nul
echo ✅ Bichosdobairro-novo removida!
echo.

echo 3. Clonando repositorio diretamente...
git clone https://github.com/andersonldias/Bichosdobairro-novo.git
echo ✅ Repositorio clonado!
echo.

echo 4. Fazendo checkout do commit especifico...
cd Bichosdobairro-novo
git checkout f843394c6822f243c8aa4a74acdcf5db4785bdb5
cd ..
echo ✅ Commit especifico carregado!
echo.

echo 5. Removendo pasta .git para evitar conflitos...
rmdir /s /q "Bichosdobairro-novo\.git" 2>nul
echo ✅ Pasta .git removida!
echo.

echo ========================================
echo ✅ REPOSITORIO PRONTO PARA MIGRACAO!
echo ========================================
echo.
echo Agora execute: migrar-repositorio-corrigido.bat
echo.
pause