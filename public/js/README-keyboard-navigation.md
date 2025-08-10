# Sistema de Navegação por Teclado

## Funcionalidades Implementadas

O sistema de navegação por teclado foi implementado para melhorar a produtividade e experiência do usuário ao preencher formulários. 

### Recursos Principais:

1. **Navegação com Enter**: Pressionar Enter avança automaticamente para o próximo campo do formulário
2. **Foco Automático**: O primeiro campo navegável recebe foco automaticamente quando um formulário é aberto
3. **Submissão Inteligente**: Quando não há mais campos para navegar, o formulário é submetido automaticamente
4. **Suporte a Abas/Etapas**: Em formulários com múltiplas etapas (como o wizard de clientes), avança para a próxima etapa
5. **Seleção de Texto**: Campos de texto são automaticamente selecionados ao receber foco

### Campos Suportados:

- Campos de texto (`input[type="text"]`)
- E-mail (`input[type="email"]`)
- Telefone (`input[type="tel"]`)
- Números (`input[type="number"]`)
- Senhas (`input[type="password"]`)
- Datas (`input[type="date"]`)
- Horários (`input[type="time"]`)
- Seleções (`select`)
- Áreas de texto (`textarea`)

### Campos Excluídos:

- Campos somente leitura (`[readonly]`)
- Campos desabilitados (`[disabled]`)
- Campos marcados com `[data-no-enter]`
- Campos com classe `.no-enter`

### Comportamentos Especiais:

1. **Textarea**: Em campos de texto longo, Enter só navega se Ctrl ou Shift estiver pressionado, caso contrário permite quebra de linha
2. **Botões Submit**: Enter em botões de submit mantém o comportamento padrão
3. **Formulários com Etapas**: Detecta automaticamente botões "Próximo" e funções `nextStep()`
4. **Modais**: Reinicializa automaticamente quando modais são abertos

### Formulários Integrados:

- ✅ Cadastro de Clientes (wizard)
- ✅ Cadastro de Clientes (wizard simplificado)
- ✅ Cadastro de Pets
- ✅ Agendamentos
- ✅ Login
- ✅ Todos os outros formulários (automático)

### Como Usar:

1. **Navegação Normal**: Simplesmente pressione Enter em qualquer campo para avançar
2. **Pular Navegação**: Adicione `data-no-enter="true"` ou classe `no-enter` a campos específicos
3. **Reinicializar**: Use `window.initKeyboardNavigation('#formId')` para reinicializar um formulário específico
4. **Desabilitar Campo**: Use `window.disableKeyboardNavigation('.campo-selector')` para desabilitar navegação em campos específicos

### Teclas de Atalho:

- **Enter**: Avança para próximo campo ou submete formulário
- **Ctrl+Enter**: Em textarea, força navegação para próximo campo
- **Shift+Enter**: Em textarea, força navegação para próximo campo
- **Esc**: Fecha modais (comportamento existente mantido)

### Compatibilidade:

- ✅ Formulários estáticos
- ✅ Formulários dinâmicos (modais)
- ✅ Formulários com múltiplas etapas
- ✅ Formulários carregados via AJAX
- ✅ Campos adicionados dinamicamente

### Instalação:

O sistema é carregado automaticamente em todas as páginas através do `layout.php`. Não é necessária configuração adicional.

### Personalização:

Para personalizar o comportamento, edite o arquivo `js/keyboard-navigation.js` e ajuste as configurações no objeto `config` no início do arquivo.