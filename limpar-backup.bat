@echo off
echo Removendo backup antigo...
cd /d "c:\bichosdobairro-php"
rmdir /s /q "backup-projeto-original-2025-13-08" 2>nul
echo ✅ Backup removido!
pause