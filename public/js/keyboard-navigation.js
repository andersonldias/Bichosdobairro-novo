/**
 * Sistema de Navegação por Teclado
 * Permite navegar entre campos com Enter e salvar formulários
 */

(function() {
    'use strict';

    // Configuração global
    const config = {
        // Seletores de campos que devem ser incluídos na navegação
        fieldSelectors: [
            'input[type="text"]',
            'input[type="email"]', 
            'input[type="tel"]',
            'input[type="number"]',
            'input[type="password"]',
            'input[type="date"]',
            'input[type="time"]',
            'select',
            'textarea'
        ],
        // Campos que devem ser ignorados
        excludeSelectors: [
            '[readonly]',
            '[disabled]',
            '[data-no-enter]',
            '.no-enter'
        ],
        // Seletores de botões de submit
        submitSelectors: [
            'button[type="submit"]',
            'input[type="submit"]',
            '.btn-submit',
            '#submitBtn',
            '#nextBtn'
        ]
    };

    // Função para obter todos os campos navegáveis em um formulário
    function getNavigableFields(form) {
        const allFields = form.querySelectorAll(config.fieldSelectors.join(','));
        return Array.from(allFields).filter(field => {
            // Verificar se o campo não está excluído
            const isExcluded = config.excludeSelectors.some(selector => 
                field.matches(selector)
            );
            
            // Verificar se o campo está visível
            const isVisible = field.offsetParent !== null && 
                             getComputedStyle(field).display !== 'none' &&
                             getComputedStyle(field).visibility !== 'hidden';
            
            return !isExcluded && isVisible;
        });
    }

    // Função para selecionar automaticamente o texto de um campo
    function autoSelectText(field) {
        // Verificar se é um campo de texto que pode ter seleção
        const textTypes = ['text', 'email', 'tel', 'number', 'password', 'search', 'url'];
        
        if (field.tagName === 'INPUT' && textTypes.includes(field.type)) {
            // Usar setTimeout para garantir que o foco seja aplicado primeiro
            setTimeout(() => {
                try {
                    field.select();
                    // Para dispositivos móveis, também definir a posição do cursor
                    if (field.setSelectionRange && field.value.length > 0) {
                        field.setSelectionRange(0, field.value.length);
                    }
                } catch (e) {
                    // Ignorar erros de seleção em campos especiais
                }
            }, 10);
        } else if (field.tagName === 'TEXTAREA') {
            // Para textarea, selecionar todo o conteúdo se houver
            setTimeout(() => {
                try {
                    if (field.value.length > 0) {
                        field.select();
                    }
                } catch (e) {
                    // Ignorar erros de seleção
                }
            }, 10);
        }
    }

    // Função para focar no próximo campo
    function focusNextField(currentField, form) {
        const fields = getNavigableFields(form);
        const currentIndex = fields.indexOf(currentField);
        
        if (currentIndex >= 0 && currentIndex < fields.length - 1) {
            const nextField = fields[currentIndex + 1];
            nextField.focus();
            
            // Selecionar automaticamente o texto do próximo campo
            autoSelectText(nextField);
            
            return true;
        }
        
        return false;
    }

    // Função para submeter o formulário ou avançar para próxima etapa
    function submitOrNext(form) {
        // Verificar se existe botão "Próximo" visível
        const nextBtn = form.querySelector('#nextBtn');
        if (nextBtn && !nextBtn.classList.contains('hidden') && nextBtn.style.display !== 'none') {
            nextBtn.click();
            return;
        }

        // Verificar se existe função nextStep() global
        if (typeof window.nextStep === 'function') {
            window.nextStep();
            return;
        }

        // Procurar por botão de submit visível
        const submitBtn = form.querySelector(config.submitSelectors.join(','));
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.click();
            return;
        }

        // Como último recurso, submeter o formulário diretamente
        form.submit();
    }

    // Função para lidar com o evento keydown
    function handleKeyDown(event) {
        // Verificar se foi pressionado Enter
        if (event.key !== 'Enter') {
            return;
        }

        const target = event.target;
        const form = target.closest('form');
        
        // Se não estiver em um formulário, ignorar
        if (!form) {
            return;
        }

        // Se for um textarea e não tiver Ctrl/Shift pressionado, permitir quebra de linha
        if (target.tagName === 'TEXTAREA' && !event.ctrlKey && !event.shiftKey) {
            return;
        }

        // Se for um botão de submit, deixar o comportamento padrão
        if (target.type === 'submit' || target.classList.contains('btn-submit')) {
            return;
        }

        // Prevenir o comportamento padrão
        event.preventDefault();

        // Tentar focar no próximo campo
        const movedToNext = focusNextField(target, form);
        
        // Se não conseguiu mover para o próximo campo, submeter/avançar
        if (!movedToNext) {
            submitOrNext(form);
        }
    }

    // Função para inicializar a navegação em um formulário específico
    function initializeForm(form) {
        // Adicionar atributo para identificar que já foi inicializado
        if (form.hasAttribute('data-keyboard-nav-initialized')) {
            return;
        }
        
        form.setAttribute('data-keyboard-nav-initialized', 'true');
        
        // Adicionar listener para keydown
        form.addEventListener('keydown', handleKeyDown);
        
        // Adicionar listener para focus nos campos de input para seleção automática
        const fields = getNavigableFields(form);
        fields.forEach(field => {
            // Evitar adicionar múltiplos listeners
            if (!field.hasAttribute('data-auto-select-initialized')) {
                field.setAttribute('data-auto-select-initialized', 'true');
                
                field.addEventListener('focus', function() {
                    // Aplicar seleção automática quando o campo receber foco
                    autoSelectText(this);
                });
            }
        });
        
        // Focar no primeiro campo navegável quando o formulário for exibido
        const firstField = getNavigableFields(form)[0];
        if (firstField) {
            // Usar setTimeout para garantir que o formulário esteja visível
            setTimeout(() => {
                if (form.offsetParent !== null) {
                    firstField.focus();
                    // Selecionar automaticamente o texto do primeiro campo
                    autoSelectText(firstField);
                }
            }, 100);
        }
    }

    // Função para inicializar todos os formulários na página
    function initializeAllForms() {
        const forms = document.querySelectorAll('form');
        forms.forEach(initializeForm);
    }

    // Observer para detectar novos formulários adicionados dinamicamente
    function setupMutationObserver() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Verificar se o próprio nó é um formulário
                        if (node.tagName === 'FORM') {
                            initializeForm(node);
                        }
                        
                        // Verificar se contém formulários
                        const forms = node.querySelectorAll ? node.querySelectorAll('form') : [];
                        forms.forEach(initializeForm);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Função pública para reinicializar um formulário específico
    window.initKeyboardNavigation = function(formSelector) {
        const form = typeof formSelector === 'string' ? 
                    document.querySelector(formSelector) : formSelector;
        
        if (form && form.tagName === 'FORM') {
            // Remover inicialização anterior
            form.removeAttribute('data-keyboard-nav-initialized');
            initializeForm(form);
        }
    };

    // Função pública para desabilitar navegação em um campo específico
    window.disableKeyboardNavigation = function(fieldSelector) {
        const fields = document.querySelectorAll(fieldSelector);
        fields.forEach(field => {
            field.setAttribute('data-no-enter', 'true');
        });
    };

    // Inicializar quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeAllForms();
            setupMutationObserver();
        });
    } else {
        initializeAllForms();
        setupMutationObserver();
    }

    // Reinicializar quando modais forem abertos (para formulários dinâmicos)
    document.addEventListener('click', function(event) {
        const target = event.target;
        
        // Detectar abertura de modais comuns
        if (target.matches('[data-modal-target], .modal-trigger, .open-modal')) {
            setTimeout(initializeAllForms, 200);
        }
    });

})();