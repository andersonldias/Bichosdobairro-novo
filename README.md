# Sistema de Agendamentos - Bichos do Bairro

Sistema completo de agendamentos para petshop com funcionalidades de agendamentos recorrentes, gestão de clientes e pets.

## Estrutura do Projeto

```
bichosdobairro-php/
├── public/          # Arquivos públicos (web root)
├── src/             # Classes PHP do sistema
├── sql/             # Scripts SQL essenciais
├── vendor/          # Dependências Composer
├── composer.json    # Configuração do Composer
├── config_agenda.json # Configurações do sistema
└── env.example      # Exemplo de variáveis de ambiente
```

## Funcionalidades Principais

- **Agendamentos**: Sistema completo de agendamentos com calendário
- **Agendamentos Recorrentes**: Criação e gestão de agendamentos que se repetem
- **Gestão de Clientes**: Cadastro e consulta de clientes
- **Gestão de Pets**: Cadastro e consulta de pets
- **Sistema de Login**: Autenticação segura com níveis de acesso
- **Relatórios**: Geração de relatórios de agendamentos
- **Notificações**: Sistema de notificações

## Instalação

1. Configure o arquivo `.env` baseado no `env.example`
2. Execute os scripts SQL na pasta `sql/`
3. Configure o web server para apontar para a pasta `public/`
4. Acesse o sistema e crie o primeiro usuário administrador

## Configuração do Web Server

### Apache
```apache
DocumentRoot /caminho/para/bichosdobairro-php/public
```

### Nginx
```nginx
root /caminho/para/bichosdobairro-php/public;
```

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Extensões PHP: mysqli, json, mbstring

## Segurança

- Todas as senhas são criptografadas
- Sistema de sessões seguro
- Validação de entrada de dados
- Proteção contra SQL injection 