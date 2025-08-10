/**
 * Sistema de Busca de Endereços
 * Integração com ViaCEP e Google Maps API
 * Sistema Bichos do Bairro
 */

class AddressSearch {
    constructor() {
        this.googleMapsApiKey = null;
        this.monthlyUsage = 0;
        this.monthlyLimit = 10000; // Limite gratuito da categoria Essentials
        this.warningThreshold = 0.8; // 80% do limite
        this.currentMonth = new Date().getMonth();
        this.currentYear = new Date().getFullYear();
        
        this.init();
    }

    /**
     * Inicializa o sistema de busca de endereços
     */
    async init() {
        await this.loadConfig();
        this.loadUsageData();
        this.setupEventListeners();
        console.log('AddressSearch inicializado. API Key:', !!this.googleMapsApiKey);
    }

    /**
     * Carrega configurações da API
     */
    async loadConfig() {
        try {
            const response = await fetch('/api/config/google-maps.php');
            const config = await response.json();
            this.googleMapsApiKey = config.api_key;
            this.monthlyLimit = config.monthly_limit || 10000;
            console.log('Google Maps API configurada:', config.enabled ? 'Sim' : 'Não');
        } catch (error) {
            console.warn('Configuração do Google Maps não encontrada, usando apenas ViaCEP');
        }
    }

    /**
     * Carrega dados de uso do localStorage
     */
    loadUsageData() {
        const storageKey = `gmaps_usage_${this.currentYear}_${this.currentMonth}`;
        const stored = localStorage.getItem(storageKey);
        this.monthlyUsage = stored ? parseInt(stored) : 0;
        
        // Resetar se mudou o mês
        const now = new Date();
        if (now.getMonth() !== this.currentMonth || now.getFullYear() !== this.currentYear) {
            this.monthlyUsage = 0;
            this.currentMonth = now.getMonth();
            this.currentYear = now.getFullYear();
            this.saveUsageData();
        }
    }

    /**
     * Salva dados de uso
     */
    saveUsageData() {
        const storageKey = `gmaps_usage_${this.currentYear}_${this.currentMonth}`;
        localStorage.setItem(storageKey, this.monthlyUsage.toString());
    }

    /**
     * Incrementa contador de uso e verifica limites
     */
    incrementUsage() {
        this.monthlyUsage++;
        this.saveUsageData();
        this.checkUsageWarning();
    }

    /**
     * Verifica se deve exibir avisos de uso
     */
    checkUsageWarning() {
        const percentage = this.monthlyUsage / this.monthlyLimit;
        
        if (percentage >= 1.0) {
            this.showUsageLimitReached();
        } else if (percentage >= this.warningThreshold) {
            this.showUsageWarning(percentage);
        }
    }

    /**
     * Exibe aviso de 80% do limite atingido
     */
    showUsageWarning(percentage) {
        const percentageText = Math.round(percentage * 100);
        const remaining = this.monthlyLimit - this.monthlyUsage;
        
        const message = `⚠️ Aviso: ${percentageText}% do limite mensal da API Google Maps foi atingido.\n` +
                       `Uso atual: ${this.monthlyUsage}/${this.monthlyLimit}\n` +
                       `Restam: ${remaining} consultas este mês.\n\n` +
                       `Após o limite, será cobrado US$ 5,00 por 1.000 consultas adicionais.`;
        
        // Exibir apenas uma vez por sessão
        if (!sessionStorage.getItem('gmaps_warning_shown')) {
            alert(message);
            sessionStorage.setItem('gmaps_warning_shown', 'true');
        }
        
        // Log para administradores
        console.warn('Google Maps API - Limite de 80% atingido:', {
            usage: this.monthlyUsage,
            limit: this.monthlyLimit,
            percentage: percentageText + '%'
        });
    }

    /**
     * Exibe aviso de limite atingido
     */
    showUsageLimitReached() {
        const message = `🚫 Limite mensal da API Google Maps atingido!\n` +
                       `Uso: ${this.monthlyUsage}/${this.monthlyLimit}\n\n` +
                       `Novas consultas serão cobradas. Usando apenas busca por CEP.`;
        
        if (!sessionStorage.getItem('gmaps_limit_shown')) {
            alert(message);
            sessionStorage.setItem('gmaps_limit_shown', 'true');
        }
    }

    /**
     * Configura event listeners para campos de endereço
     */
    setupEventListeners() {
        // Busca por CEP
        document.addEventListener('input', (e) => {
            if (e.target.name === 'cep' || e.target.id === 'cep') {
                this.handleCepInput(e.target);
            }
        });

        // Busca por nome de rua
        document.addEventListener('input', (e) => {
            if (e.target.dataset.addressSearch === 'true') {
                this.handleAddressInput(e.target);
            }
        });
    }

    /**
     * Manipula entrada de CEP
     */
    handleCepInput(field) {
        const cep = field.value.replace(/\D/g, '');
        
        if (cep.length === 8) {
            this.searchByCep(cep);
        }
    }

    /**
     * Manipula entrada de endereço
     */
    handleAddressInput(field) {
        const query = field.value.trim();
        
        if (query.length >= 3) {
            // Debounce para evitar muitas consultas
            clearTimeout(this.addressTimeout);
            this.addressTimeout = setTimeout(() => {
                this.searchByAddress(query, field);
            }, 500);
        }
    }

    /**
     * Busca endereço por CEP usando ViaCEP
     */
    async searchByCep(cep) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (!data.erro) {
                this.fillAddressFields({
                    logradouro: data.logradouro,
                    bairro: data.bairro,
                    cidade: data.localidade,
                    estado: data.uf,
                    cep: data.cep
                });
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        }
    }

    /**
     * Busca endereço por nome usando Google Maps API
     */
    async searchByAddress(query, sourceField) {
        console.log('searchByAddress chamado para:', query);
        console.log('API Key disponível:', !!this.googleMapsApiKey);
        console.log('Uso mensal atual:', this.monthlyUsage, '/', this.monthlyLimit);
        
        // Verificar se API está disponível e dentro do limite
        if (!this.googleMapsApiKey) {
            console.warn('Google Maps API não configurada');
            return;
        }

        if (this.monthlyUsage >= this.monthlyLimit) {
            console.warn('Limite mensal da Google Maps API atingido');
            return;
        }

        try {
            const url = `https://maps.googleapis.com/maps/api/geocode/json?` +
                       `address=${encodeURIComponent(query + ', Brasil')}&` +
                       `key=${this.googleMapsApiKey}&` +
                       `language=pt-BR&` +
                       `region=BR`;

            const response = await fetch(url);
            const data = await response.json();
            
            // Incrementar contador de uso
            this.incrementUsage();

            if (data.status === 'OK' && data.results.length > 0) {
                console.log('Resultados encontrados:', data.results.length);
                // Sempre mostra sugestões para o usuário escolher
                this.showAddressSuggestions(data.results, sourceField);
            } else {
                console.warn('Endereço não encontrado:', data.status);
            }
        } catch (error) {
            console.error('Erro ao buscar endereço:', error);
        }
    }

    /**
     * Exibe sugestões de endereço
     */
    showAddressSuggestions(results, sourceField) {
        // Remove sugestões anteriores
        this.removeSuggestions();
        
        const suggestionsDiv = document.createElement('div');
        suggestionsDiv.className = 'address-suggestions';
        suggestionsDiv.style.cssText = `
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            width: ${sourceField.offsetWidth}px;
        `;
        
        results.slice(0, 5).forEach(result => {
            const suggestion = document.createElement('div');
            suggestion.className = 'address-suggestion';
            suggestion.style.cssText = `
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
            `;
            suggestion.textContent = result.formatted_address;
            
            suggestion.addEventListener('click', () => {
                const parsed = this.parseGoogleMapsResult(result);
                this.fillAddressFields(parsed);
                this.removeSuggestions();
            });
            
            suggestion.addEventListener('mouseenter', () => {
                suggestion.style.backgroundColor = '#f5f5f5';
            });
            
            suggestion.addEventListener('mouseleave', () => {
                suggestion.style.backgroundColor = 'white';
            });
            
            suggestionsDiv.appendChild(suggestion);
        });
        
        // Posicionar sugestões
        sourceField.parentNode.style.position = 'relative';
        sourceField.parentNode.appendChild(suggestionsDiv);
        
        // Fechar sugestões ao clicar fora
        document.addEventListener('click', (e) => {
            if (!suggestionsDiv.contains(e.target) && e.target !== sourceField) {
                this.removeSuggestions();
            }
        });
    }

    /**
     * Remove sugestões de endereço
     */
    removeSuggestions() {
        const suggestions = document.querySelectorAll('.address-suggestions');
        suggestions.forEach(s => s.remove());
    }

    /**
     * Analisa resultado do Google Maps
     */
    parseGoogleMapsResult(result) {
        const components = result.address_components;
        const parsed = {
            logradouro: '',
            numero: '',
            bairro: '',
            cidade: '',
            estado: '',
            cep: ''
        };
        
        components.forEach(component => {
            const types = component.types;
            
            if (types.includes('route')) {
                parsed.logradouro = component.long_name;
            } else if (types.includes('street_number')) {
                parsed.numero = component.long_name;
            } else if (types.includes('sublocality') || types.includes('neighborhood')) {
                parsed.bairro = component.long_name;
            } else if (types.includes('locality') || types.includes('administrative_area_level_2')) {
                parsed.cidade = component.long_name;
            } else if (types.includes('administrative_area_level_1')) {
                parsed.estado = component.short_name;
            } else if (types.includes('postal_code')) {
                parsed.cep = component.long_name;
            }
        });
        
        return parsed;
    }

    /**
     * Preenche campos de endereço
     */
    fillAddressFields(data) {
        const fields = {
            'logradouro': data.logradouro,
            'endereco': data.logradouro,
            'rua': data.logradouro,
            'numero': data.numero,
            'bairro': data.bairro,
            'cidade': data.cidade,
            'localidade': data.cidade,
            'estado': data.estado,
            'uf': data.estado,
            'cep': data.cep
        };
        
        Object.keys(fields).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"], #${fieldName}`);
            if (field && fields[fieldName]) {
                field.value = fields[fieldName];
                // Disparar evento de mudança
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    /**
     * Obtém estatísticas de uso
     */
    getUsageStats() {
        return {
            monthlyUsage: this.monthlyUsage,
            monthlyLimit: this.monthlyLimit,
            percentage: Math.round((this.monthlyUsage / this.monthlyLimit) * 100),
            remaining: this.monthlyLimit - this.monthlyUsage
        };
    }

    /**
     * Retorna configurações da API
     */
    async getConfig() {
        return {
            enabled: !!this.googleMapsApiKey,
            api_key: this.googleMapsApiKey,
            usage_count: this.monthlyUsage,
            monthly_limit: this.monthlyLimit
        };
    }
}

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.addressSearch = new AddressSearch();
    });
} else {
    window.addressSearch = new AddressSearch();
}