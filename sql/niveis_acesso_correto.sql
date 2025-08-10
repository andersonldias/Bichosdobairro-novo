-- Tabela de níveis de acesso personalizáveis
CREATE TABLE IF NOT EXISTS niveis_acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100 NOT NULL UNIQUE,
    descricao TEXT,
    cor VARCHAR(7) DEFAULT#667ea',
    ativo TINYINT(1) DEFAULT1    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de permissões por área
CREATE TABLE IF NOT EXISTS permissoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100 NOT NULL UNIQUE,
    descricao TEXT,
    area VARCHAR(50) NOT NULL,
    ativo TINYINT(1) DEFAULT1    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de relacionamento entre níveis e permissões
CREATE TABLE IF NOT EXISTS nivel_permissoes (
    nivel_id INT NOT NULL,
    permissao_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (nivel_id, permissao_id),
    FOREIGN KEY (nivel_id) REFERENCES niveis_acesso(id) ON DELETE CASCADE,
    FOREIGN KEY (permissao_id) REFERENCES permissoes(id) ON DELETE CASCADE
);

-- Inserir níveis padrão
INSERT INTO niveis_acesso (nome, descricao, cor) VALUES
(admin', 'Administrador completo do sistema',#dc2626,
('gerente', 'Gerente com acesso amplo', '#059669),
('usuario,Usuário básico', #667ea),('operador,Operador de agendamentos',#d97706),
('tosador',Tosador especializado, #7c3aed'),
('veterinario',Veterinário', #891),
(recepcionista', 'Recepcionista', #59669)
ON DUPLICATE KEY UPDATE nome = nome;

-- Inserir permissões padrão
INSERT INTO permissoes (nome, descricao, area) VALUES
-- Dashboard
('dashboard_visualizar', 'Visualizar dashboard,dashboard'),

-- Usuários
('usuarios_visualizar',Visualizar lista de usuários,usuarios),
('usuarios_criar', 'Criar novos usuários,usuarios'),
(usuarios_editar', 'Editar usuários existentes,usuarios'),
('usuarios_excluir',Excluir usuários,usuarios'),
('usuarios_alterar_senha,Alterar senhas de usuários', 'usuarios'),

-- Clientes
('clientes_visualizar',Visualizar lista de clientes, ientes),
('clientes_criar', 'Criar novos clientes, ientes'),
(clientes_editar', 'Editar clientes existentes, ientes'),
('clientes_excluir,Excluir clientes', 'clientes'),

-- Pets
(pets_visualizar',Visualizar lista de pets,pets),
(pets_criar',Criar novos pets, pets'),
(pets_editar', Editar pets existentes,pets),
('pets_excluir, Excluir pets,pets,

-- Agendamentos
('agendamentos_visualizar',Visualizar agendamentos',agendamentos),
('agendamentos_criar,Criarnovos agendamentos',agendamentos),
('agendamentos_editar', 'Editar agendamentos',agendamentos),
(agendamentos_excluir', 'Excluir agendamentos', agendamentos),

-- Caixa
('caixa_visualizar',Visualizar caixa',caixa),
('caixa_lancar', 'Lançar movimentações no caixa', caixa),
('caixa_editar', 'Editar movimentações',caixa'),
(caixa_excluir',Excluir movimentações',caixa),

-- Relatórios
(relatorios_visualizar,Visualizar relatórios,relatorios'),
(relatorios_exportar', 'Exportar relatórios', relatorios'),

-- Configurações
('configuracoes_visualizar', 'Visualizar configurações', configuracoes'),
('configuracoes_editar',Editar configurações',configuracoes'),

-- Administração
('admin_niveis_gerenciar',Gerenciar níveis de acesso', admin'),
(admin_permissoes_gerenciar', Gerenciar permissões',admin'),
(admin_sistema_gerenciar', Gerenciar sistema', admin')
ON DUPLICATE KEY UPDATE nome = nome;

-- Atribuir todas as permissões ao admin
INSERT INTO nivel_permissoes (nivel_id, permissao_id)
SELECT na.id, p.id
FROM niveis_acesso na
CROSS JOIN permissoes p
WHERE na.nome =admin
ON DUPLICATE KEY UPDATE nivel_id = nivel_id;

-- Atribuir permissões básicas ao usuário
INSERT INTO nivel_permissoes (nivel_id, permissao_id)
SELECT na.id, p.id
FROM niveis_acesso na
CROSS JOIN permissoes p
WHERE na.nome = usuario' 
AND p.nome IN (
 dashboard_visualizar',
clientes_visualizar',
pets_visualizar,
  'agendamentos_visualizar'
)
ON DUPLICATE KEY UPDATE nivel_id = nivel_id;

-- Atribuir permissões ao gerente
INSERT INTO nivel_permissoes (nivel_id, permissao_id)
SELECT na.id, p.id
FROM niveis_acesso na
CROSS JOIN permissoes p
WHERE na.nome = gerente' 
AND p.nome IN (
 dashboard_visualizar',
clientes_visualizar,
  clientes_criar',
clientes_editar',
pets_visualizar,
  pets_criar',
  pets_editar,
  'agendamentos_visualizar,
 agendamentos_criar,
agendamentos_editar,caixa_visualizar, caixa_lancar',
  relatorios_visualizar'
)
ON DUPLICATE KEY UPDATE nivel_id = nivel_id;

-- Atribuir permissões ao operador
INSERT INTO nivel_permissoes (nivel_id, permissao_id)
SELECT na.id, p.id
FROM niveis_acesso na
CROSS JOIN permissoes p
WHERE na.nome =operador' 
AND p.nome IN (
 dashboard_visualizar,
  'agendamentos_visualizar,
 agendamentos_criar,
agendamentos_editar',
clientes_visualizar,
  clientes_criar',
pets_visualizar,
 pets_criar'
)
ON DUPLICATE KEY UPDATE nivel_id = nivel_id;

-- Atribuir permissões ao tosador
INSERT INTO nivel_permissoes (nivel_id, permissao_id)
SELECT na.id, p.id
FROM niveis_acesso na
CROSS JOIN permissoes p
WHERE na.nome = tosador' 
AND p.nome IN (
 dashboard_visualizar,
  'agendamentos_visualizar,
agendamentos_editar',
clientes_visualizar',
pets_visualizar',
  pets_editar'
)
ON DUPLICATE KEY UPDATE nivel_id = nivel_id;

-- Atribuir permissões ao veterinário
INSERT INTO nivel_permissoes (nivel_id, permissao_id)
SELECT na.id, p.id
FROM niveis_acesso na
CROSS JOIN permissoes p
WHERE na.nome = veterinario' 
AND p.nome IN (
 dashboard_visualizar,
  'agendamentos_visualizar,
agendamentos_editar',
clientes_visualizar',
pets_visualizar',
  pets_editar',
  relatorios_visualizar'
)
ON DUPLICATE KEY UPDATE nivel_id = nivel_id;

-- Atribuir permissões ao recepcionista
INSERT INTO nivel_permissoes (nivel_id, permissao_id)
SELECT na.id, p.id
FROM niveis_acesso na
CROSS JOIN permissoes p
WHERE na.nome =recepcionista' 
AND p.nome IN (
 dashboard_visualizar,
  'agendamentos_visualizar,
 agendamentos_criar,
agendamentos_editar',
clientes_visualizar,
  clientes_criar',
clientes_editar',
pets_visualizar',
  pets_criar',
  pets_editar,caixa_visualizar,
 caixa_lancar'
)
ON DUPLICATE KEY UPDATE nivel_id = nivel_id; 