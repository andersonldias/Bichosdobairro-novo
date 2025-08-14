@echo off
echo Iniciando limpeza completa do sistema...
echo.

echo Removendo pasta duplicada src/src/...
rmdir /s /q "src\src"

echo Removendo arquivos de teste...
del /q "public\teste-*.php" 2>nul
del /q "public\teste-*.html" 2>nul
del /q "teste-*.php" 2>nul
del /q "testar-*.php" 2>nul

echo Removendo arquivos de debug...
del /q "public\debug-*.php" 2>nul
del /q "public\debug_*.php" 2>nul
del /q "public\debug_*.txt" 2>nul

echo Removendo arquivos de correção...
del /q "public\corrigir-*.php" 2>nul

echo Removendo arquivos de diagnóstico...
del /q "public\diagnostico-*.php" 2>nul

echo Removendo arquivos de verificação...
del /q "public\verificar-*.php" 2>nul
del /q "verificar-*.php" 2>nul

echo Removendo utilitários de desenvolvimento...
del /q "public\limpar-*.php" 2>nul
del /q "public\monitor-*.php" 2>nul
del /q "public\verificacao-*.php" 2>nul

echo Removendo scripts de criação temporários...
del /q "public\criar-banco-completo.php" 2>nul
del /q "public\criar-banco-corrigido.php" 2>nul
del /q "public\criar-tabela-*.php" 2>nul
del /q "public\resetar-*.php" 2>nul
del /q "public\instalar-login.php" 2>nul

echo Removendo arquivos específicos de desenvolvimento...
del /q "public\clientes-debug.php" 2>nul
del /q "public\clientes-teste-logado.php" 2>nul
del /q "public\credenciais-teste.php" 2>nul
del /q "public\aplicar-mudancas-clientes.php" 2>nul
del /q "public\backup-automatico.php" 2>nul
del /q "public\deploy-web.php" 2>nul

echo Removendo outros arquivos desnecessários...
del /q "te-validacao-telefone.php" 2>nul
del /q "tatus" 2>nul

echo.
echo ✅ Limpeza completa finalizada!
echo ✅ Sistema otimizado para produção!
echo.
pause