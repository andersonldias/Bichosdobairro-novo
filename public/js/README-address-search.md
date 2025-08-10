# Sistema de Busca de Endereços

Sistema integrado de busca de endereços que combina ViaCEP (gratuito) e Google Maps API (pago) com monitoramento de uso.

## Funcionalidades

### 1. Busca por CEP (ViaCEP - Gratuito)
- Busca automática ao digitar 8 dígitos no campo CEP
- Preenchimento automático dos campos: logradouro, bairro, cidade, estado
- Funciona sem configuração adicional

### 2. Busca por Nome de Rua (Google Maps API - Pago)
- Busca por nome de rua, avenida, etc.
- Sugestões em tempo real
- Preenchimento automático de todos os campos de endereço
- **Requer configuração da API do Google Maps**

### 3. Monitoramento de Uso
- Controle automático do uso mensal da API Google Maps
- Alerta ao atingir 80% do limite gratuito
- Bloqueio automático ao atingir 100% do limite
- Logs detalhados de uso

## Configuração

### 1. Configurar Google Maps API

1. Acesse o [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um novo projeto ou selecione um existente
3. Ative a **Geocoding API**
4. Crie uma chave de API
5. Configure restrições de segurança (recomendado)

### 2. Configurar no Sistema

1. Copie o arquivo `.env.example` para `.env`
2. Configure as variáveis:

```env
# Configurações Google Maps API
GOOGLE_MAPS_API_KEY=sua_chave_da_api_google_maps_aqui
GOOGLE_MAPS_MONTHLY_LIMIT=10000
GOOGLE_MAPS_WARNING_THRESHOLD=0.8
```

### 3. Parâmetros de Configuração

- **GOOGLE_MAPS_API_KEY**: Sua chave da API do Google Maps
- **GOOGLE_MAPS_MONTHLY_LIMIT**: Limite mensal de consultas (padrão: 10.000)
- **GOOGLE_MAPS_WARNING_THRESHOLD**: Percentual para alerta (padrão: 0.8 = 80%)

## Como Usar

### Para Desenvolvedores

#### Habilitar busca por nome de rua em um campo:

```html
<input type="text" 
       name="logradouro" 
       id="endereco" 
       data-address-search="true"
       placeholder="Digite o nome da rua...">
```

#### Acessar estatísticas de uso:

```javascript
// Obter estatísticas atuais
const stats = window.addressSearch.getUsageStats();
console.log(stats);
// Resultado:
// {
//   monthlyUsage: 1250,
//   monthlyLimit: 10000,
//   percentage: 13,
//   remaining: 8750
// }
```

### Para Usuários

#### Busca por CEP:
1. Digite o CEP no campo correspondente
2. Os campos de endereço serão preenchidos automaticamente

#### Busca por Nome de Rua:
1. Digite pelo menos 3 caracteres no campo "Logradouro"
2. Aguarde as sugestões aparecerem
3. Clique na sugestão desejada
4. Todos os campos serão preenchidos automaticamente

## Limites e Custos

### Google Maps API (2025)

**Limites Gratuitos por Categoria:**
- **Essentials**: 10.000 chamadas/mês
- **Pro**: 5.000 chamadas/mês
- **Enterprise**: 1.000 chamadas/mês

**Custos após limite gratuito:**
- Geocoding API: US$ 5,00 por 1.000 requisições

**Descontos por Volume:**
- 20% para uso acima de 100.000 requisições/mês
- Até 80% para uso acima de 5 milhões/mês

### ViaCEP
- **Totalmente gratuito**
- Sem limite de consultas
- Apenas para CEPs brasileiros

## Monitoramento

### Alertas Automáticos

1. **80% do limite**: Exibe alerta no navegador
2. **100% do limite**: Bloqueia novas consultas e exibe aviso
3. **Logs**: Registra uso elevado nos logs do sistema

### Verificar Uso Atual

```javascript
// No console do navegador
window.addressSearch.getUsageStats();
```

### Dados Armazenados

- **LocalStorage**: Contador mensal por usuário
- **Banco de Dados**: Histórico consolidado (tabela `google_maps_usage`)

## Segurança

### Restrições Recomendadas para API Key

1. **Restrição por HTTP referrer**:
   - `https://seudominio.com/*`
   - `http://localhost:8000/*` (desenvolvimento)

2. **Restrição por API**:
   - Geocoding API apenas

3. **Monitoramento**:
   - Configure alertas no Google Cloud Console
   - Monitore uso diário/mensal

## Troubleshooting

### Problemas Comuns

1. **Busca por nome não funciona**:
   - Verifique se a API key está configurada
   - Verifique se a Geocoding API está ativada
   - Verifique restrições da API key

2. **Erro de CORS**:
   - Verifique restrições de HTTP referrer
   - Adicione seu domínio nas restrições

3. **Limite atingido muito rápido**:
   - Verifique se há loops ou chamadas desnecessárias
   - Considere aumentar o debounce (padrão: 500ms)

### Logs de Debug

Abra o console do navegador (F12) para ver logs detalhados:

```javascript
// Habilitar logs detalhados
window.addressSearch.debugMode = true;
```

## API Endpoints

### GET /api/config/google-maps
Retorna configurações da API

```json
{
  "api_key": "AIza...",
  "monthly_limit": 10000,
  "warning_threshold": 0.8,
  "enabled": true
}
```

### POST /api/config/google-maps
Atualiza contador de uso

```json
{
  "usage_count": 1250
}
```

## Estrutura de Arquivos

```
public/
├── js/
│   ├── address-search.js          # Sistema principal
│   └── README-address-search.md    # Esta documentação
├── api/
│   └── config/
│       └── google-maps.php         # Endpoint de configuração
└── ...

src/
└── Config.php                      # Configurações do sistema
```

## Contribuição

Para contribuir com melhorias:

1. Teste thoroughly em ambiente de desenvolvimento
2. Mantenha compatibilidade com versões anteriores
3. Atualize esta documentação
4. Considere impacto nos custos da API

## Changelog

### v1.0.0 (2024)
- Implementação inicial
- Integração ViaCEP + Google Maps
- Sistema de monitoramento de uso
- Alertas automáticos de limite
- Suporte a múltiplos campos de endereço