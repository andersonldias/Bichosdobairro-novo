@echo off
echo Verificando status do Git...
git status

echo.
echo Adicionando arquivos modificados...
git add .

echo.
echo Fazendo commit das mudanças...
git commit -m "feat: implementar melhorias de UX nos formularios de clientes

- E-mail opcional e telefone obrigatorio
- Autofoco no primeiro campo
- Navegação com Enter entre campos
- Validações atualizadas
- Documentação das mudanças"

echo.
echo Enviando para o GitHub...
git push origin main

echo.
echo Commit realizado com sucesso!
pause
