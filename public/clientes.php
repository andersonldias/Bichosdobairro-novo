<?php
require_once '../src/init.php';

// Verificar se é uma requisição AJAX
if (isAjax()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'buscar':
            $termo = $_GET['termo'] ?? '';
            $clientes = Cliente::buscar($termo, ['limite' => 10]);
            jsonResponse($clientes);
            break;
            
        case 'criar':
            $dados = [
                'nome' => sanitize($_POST['nome'] ?? ''),
                'email' => sanitize($_POST['email'] ?? ''),
                'telefone' => sanitize($_POST['telefone'] ?? ''),
                'cpf' => sanitize($_POST['cpf'] ?? ''),
                'cep' => sanitize($_POST['cep'] ?? ''),
        'logradouro' => sanitize($_POST['logradouro'] ?? ''),
        'numero' => sanitize($_POST['numero'] ?? ''),
        'complemento' => sanitize($_POST['complemento'] ?? ''),
        'bairro' => sanitize($_POST['bairro'] ?? ''),
        'cidade' => sanitize($_POST['cidade'] ?? ''),
        'estado' => sanitize($_POST['estado'] ?? ''),
                'observacoes' => sanitize($_POST['observacoes'] ?? '')
            ];
            
            if (empty($dados['nome']) || empty($dados['telefone'])) {
                jsonResponse(['success' => false, 'message' => 'Nome e telefone são obrigatórios'], 400);
            }
            
            $id = Cliente::criar($dados);
            if ($id) {
                jsonResponse(['success' => true, 'id' => $id, 'message' => 'Cliente criado com sucesso']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Erro ao criar cliente'], 500);
            }
            break;
            
        default:
            jsonResponse(['error' => 'Ação não reconhecida'], 400);
    }
}

$todos = Cliente::listarTodos();

// Mensagens de feedback
$msg = '';
$erro = '';

// Edição: buscar cliente se id_edit estiver presente
$editando = false;
$cliente_edit = [
            'id' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'cep' => '', 'logradouro' => '', 'numero' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'estado' => ''
];
if (isset($_GET['id_edit'])) {
    $id_edit = $_GET['id_edit'];
    $todos = Cliente::listarTodos();
    foreach ($todos as $c) {
        if ($c['id'] == $id_edit) {
            $cliente_edit = $c;
            $editando = true;
            break;
        }
    }
}

// Cadastro ou edição de cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    try {
        $cpf = isset($_POST['cpf']) ? trim($_POST['cpf']) : null;
        $ignorarId = isset($_POST['id']) && $_POST['id'] ? $_POST['id'] : null;
        
        // Processar múltiplos telefones
        $telefones = [];
        if (isset($_POST['telefone_nome']) && isset($_POST['telefone_numero'])) {
            $nomes = $_POST['telefone_nome'];
            $numeros = $_POST['telefone_numero'];
            for ($i = 0; $i < count($nomes); $i++) {
                if (!empty($nomes[$i]) || !empty($numeros[$i])) {
                    $telefones[] = [
                        'nome' => trim($nomes[$i]),
                        'numero' => trim($numeros[$i])
                    ];
                }
            }
        }
        // Validação obrigatória do telefone
        if (empty($telefones) || empty($telefones[0]['numero'])) {
            $erro = 'Pelo menos um telefone é obrigatório.';
            header('Location: clientes.php?erro=' . urlencode($erro));
            exit;
        }
        // Receber campos detalhados de endereço
            $cep = $_POST['cep'] ?? '';
    $logradouro = $_POST['logradouro'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
        
        if ($cpf && !Cliente::validarCPF($cpf)) {
            $erro = 'CPF inválido.';
            header('Location: clientes.php?erro=' . urlencode($erro));
            exit;
        } elseif (Cliente::existeDuplicado($_POST['nome'], $_POST['email'], $cpf, $ignorarId)) {
            $erro = 'Já existe um cliente com o mesmo nome, e-mail ou CPF.';
            header('Location: clientes.php?erro=' . urlencode($erro));
            exit;
        } else {
            if ($ignorarId) {
                // Montar array de pets para passar para atualizar
                $pets = [];
                if (isset($_POST['pet_nome']) && is_array($_POST['pet_nome'])) {
                    $pet_nomes = $_POST['pet_nome'];
                    $pet_especies = $_POST['pet_especie'] ?? [];
                    $pet_racas = $_POST['pet_raca'] ?? [];
                    $pet_idades = $_POST['pet_idade'] ?? [];
                    for ($i = 0; $i < count($pet_nomes); $i++) {
                        $nome = trim($pet_nomes[$i]);
                        $especie = trim($pet_especies[$i] ?? '');
                        $raca = trim($pet_racas[$i] ?? '');
                        $idade = trim($pet_idades[$i] ?? '');
                        $idade = ($idade === '' ? null : $idade);
                        if (!empty($nome)) {
                            $pets[] = [
                                'nome' => $nome,
                                'especie' => $especie,
                                'raca' => $raca,
                                'idade' => $idade
                            ];
                        }
                    }
                }
                // Atualizar
                $dados_cliente = [
                    'nome' => $_POST['nome'],
                    'email' => $_POST['email'],
                    'telefone' => $telefones[0]['numero'], // Usar o primeiro telefone como principal
                    'cpf' => $cpf,
                    'cep' => $cep,
                    'logradouro' => $logradouro,
                    'numero' => $numero,
                    'complemento' => $complemento,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'telefones' => $telefones,
                    'pets' => $pets
                ];
                Cliente::atualizar($_POST['id'], $dados_cliente);
                $msg = 'Cliente atualizado com sucesso!';
            } else {
                // Montar array de pets para passar para Cliente::criar
                $pets = [];
                if (isset($_POST['pet_nome']) && is_array($_POST['pet_nome'])) {
                    $pet_nomes = $_POST['pet_nome'];
                    $pet_especies = $_POST['pet_especie'] ?? [];
                    $pet_racas = $_POST['pet_raca'] ?? [];
                    $pet_idades = $_POST['pet_idade'] ?? [];
                    for ($i = 0; $i < count($pet_nomes); $i++) {
                        $nome = trim($pet_nomes[$i]);
                        $especie = trim($pet_especies[$i] ?? '');
                        $raca = trim($pet_racas[$i] ?? '');
                        $idade = trim($pet_idades[$i] ?? '');
                        $idade = ($idade === '' ? null : $idade);
                        if (!empty($nome)) {
                            $pets[] = [
                                'nome' => $nome,
                                'especie' => $especie,
                                'raca' => $raca,
                                'idade' => $idade
                            ];
                        }
                    }
                }
                // Novo
                $dados_cliente = [
                    'nome' => $_POST['nome'],
                    'email' => $_POST['email'],
                    'telefone' => $telefones[0]['numero'], // Usar o primeiro telefone como principal
                    'cpf' => $cpf,
                    'cep' => $cep,
                    'logradouro' => $logradouro,
                    'numero' => $numero,
                    'complemento' => $complemento,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'telefones' => $telefones,
                    'pets' => $pets
                ];
                $cliente_id = Cliente::criar($dados_cliente);
                $msg = 'Cliente cadastrado com sucesso!';
                
                // Logar todo o POST antes de processar pets
                // Debug removido para produção
                if (!empty(array_filter($pet_nomes)) || !empty(array_filter($pet_especies))) {
                    $msg .= ' Pets também foram cadastrados.';
                }
            }
            header('Location: clientes.php?msg=' . urlencode($msg));
            exit;
        }
    } catch (Exception $e) {
        $erro = 'Erro ao salvar: ' . $e->getMessage();
        header('Location: clientes.php?erro=' . urlencode($erro));
        exit;
    }
}

// Exclusão de cliente
if (isset($_GET['excluir'])) {
    try {
        Cliente::deletar($_GET['excluir']);
        $msg = 'Cliente excluído com sucesso!';
    } catch (Exception $e) {
        $erro = 'Erro ao excluir: ' . $e->getMessage();
    }
}

// Busca e filtros avançados
$busca = $_GET['busca'] ?? '';
$filtro_campo = $_GET['filtro_campo'] ?? 'todos';
$ordenacao = $_GET['ordenacao'] ?? 'nome';
$direcao = $_GET['direcao'] ?? 'asc';

$clientes = Cliente::listarTodos();

// Aplicar filtros
if ($busca) {
    $clientes = array_filter($clientes, function($c) use ($busca, $filtro_campo) {
        if ($filtro_campo === 'todos') {
            return stripos($c['nome'], $busca) !== false || 
                   stripos($c['email'], $busca) !== false ||
                   stripos($c['telefone'], $busca) !== false ||
                   stripos($c['logradouro'] . ' ' . $c['bairro'] . ' ' . $c['cidade'] . ' ' . $c['estado'], $busca) !== false;
        } else {
            return stripos($c[$filtro_campo], $busca) !== false;
        }
    });
}

// Aplicar ordenação
usort($clientes, function($a, $b) use ($ordenacao, $direcao) {
    $valor_a = $a[$ordenacao] ?? '';
    $valor_b = $b[$ordenacao] ?? '';
    
    if ($direcao === 'asc') {
        return strcasecmp($valor_a, $valor_b);
    } else {
        return strcasecmp($valor_b, $valor_a);
    }
});

// Estatísticas
$total_clientes = count($clientes);
$clientes_com_telefone = 0;
foreach ($clientes as $cli) {
    $tels = Cliente::buscarTelefones($cli['id']);
    if (!empty($tels)) $clientes_com_telefone++;
}
$clientes_com_endereco = count(array_filter($clientes, function($c) { 
    return !empty($c['logradouro']) || !empty($c['bairro']) || !empty($c['cidade']) || !empty($c['estado']); 
}));

// Buscar telefones principais para cada cliente
$telefones_principais = [];
foreach ($clientes as $cli) {
    $tels = Cliente::buscarTelefones($cli['id']);
    $telefones_principais[$cli['id']] = isset($tels[0]['numero']) ? $tels[0]['numero'] : '';
}

// Exibir mensagem via GET se houver
$msg = $_GET['msg'] ?? $msg;
$erro = $_GET['erro'] ?? '';

// Adicionar log temporário para depuração dos telefones recebidos no POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug removido para produção
    
    // Debug adicional para telefones
    if (isset($_POST['telefone_nome']) && isset($_POST['telefone_numero'])) {
        // Debug removido para produção
    }
    
    // Debug adicional para pets
    if (isset($_POST['pet_nome'])) {
        // Debug removido para produção
    }
}

function render_content() {
    global $msg, $erro, $clientes, $busca, $filtro_campo, $ordenacao, $direcao, $editando, $cliente_edit;
    global $total_clientes, $clientes_com_telefone, $clientes_com_endereco;
    global $telefones_principais;
?>
    <div class="space-y-6">
        <!-- Mensagens -->
        <?php if ($msg): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total de Clientes</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $total_clientes ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-phone text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Com Telefone</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $clientes_com_telefone ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-map-marker-alt text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Com Endereço</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $clientes_com_endereco ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campo de Busca Simples -->
        <form method="get" class="w-full flex justify-center mb-4">
            <div class="relative w-full max-w-md">
                <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar clientes..." class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        <!-- Lista de Clientes -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Lista de Clientes (<?= $total_clientes ?>)</h3>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <button onclick="openWizard()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center">
                            <i class="fas fa-magic mr-2"></i>Novo Cliente
                        </button>
                    </div>
                    <div class="text-sm text-gray-500">
                        <?php if ($busca): ?>
                            Resultados para: "<?= htmlspecialchars($busca) ?>"
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="table-responsive">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pets</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endereço</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <?php if ($busca): ?>
                                        Nenhum cliente encontrado para "<?= htmlspecialchars($busca) ?>"
                                    <?php else: ?>
                                        Nenhum cliente cadastrado
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $c): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($c['nome']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if (!empty($telefones_principais[$c['id']])): ?>
                                        <i class="fas fa-phone text-green-500 mr-1"></i><?= htmlspecialchars($telefones_principais[$c['id']]) ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    // Buscar pets do cliente
                                    $pets_cliente = [];
                                    if (class_exists('Pet')) {
                                        $todos_pets = Pet::listarTodos();
                                        foreach ($todos_pets as $pet) {
                                            if ($pet['cliente_id'] == $c['id']) {
                                                $pets_cliente[] = $pet['nome'];
                                            }
                                        }
                                    }
                                    if (!empty($pets_cliente)) {
                                        echo '<i class="fas fa-dog text-blue-500 mr-1"></i>' . htmlspecialchars(implode(', ', $pets_cliente));
                                    } else {
                                        echo '<span class="text-gray-400">-</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    $partes = [];
                                    if (!empty($c['logradouro'])) $partes[] = $c['logradouro'];
                                    if (!empty($c['numero'])) $partes[] = $c['numero'];
                                    if (!empty($c['bairro'])) $partes[] = $c['bairro'];
                                    if (!empty($c['cidade'])) $partes[] = $c['cidade'];
                                    if (!empty($c['estado'])) $partes[] = $c['estado'];
                                    $endereco = implode(', ', $partes);
                                    
                                    if (!empty($endereco)) {
                                        echo '<i class="fas fa-map-marker-alt text-blue-500 mr-1"></i>' . htmlspecialchars($endereco);
                                    } else {
                                        echo '<span class="text-gray-400">-</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editCliente(<?= htmlspecialchars(json_encode($c)) ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit mr-1"></i>Editar
                                    </button>
                                    <a href="clientes.php?excluir=<?= $c['id'] ?>" onclick="return confirm('Excluir este cliente?')" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Excluir
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                                    </table>
                </div>
                </div>
        </div>
    </div>

    <!-- Modal Wizard -->
    <div id="wizardModal" class="fixed inset-0 bg-black bg-opacity-50 z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-8 md:p-12">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-10 md:p-12 border border-gray-200 max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <!-- Header do Wizard -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Cadastro de Cliente</h3>
                    <button onclick="closeWizard()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div id="step1" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">1</div>
                            <div class="ml-2 text-sm font-medium text-blue-500">Informações Básicas</div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step2" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                            <div class="ml-2 text-sm font-medium text-gray-500">Contato</div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step3" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
                            <div class="ml-2 text-sm font-medium text-gray-500">Endereço</div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step4" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">4</div>
                            <div class="ml-2 text-sm font-medium text-gray-500">Pets</div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step5" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">5</div>
                            <div class="ml-2 text-sm font-medium text-gray-500">Confirmação</div>
                        </div>
                    </div>
                </div>

                <!-- Formulário Wizard -->
                <form id="wizardForm" method="post" class="space-y-6 pb-8">
                    <input type="hidden" name="id" id="wizard_id">
                    <!-- Step 1: Informações Básicas -->
                    <div id="step1Content" class="step-content">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Informações Básicas</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                                <input type="text" name="nome" id="wizard_nome" placeholder="Digite o nome completo" required autofocus
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div id="feedback_nome" class="text-sm mt-1"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                <input type="email" name="email" id="wizard_email" placeholder="email@exemplo.com" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div id="feedback_email" class="text-sm mt-1"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CPF *</label>
                                <input type="text" name="cpf" id="wizard_cpf" placeholder="000.000.000-00" required 
                                       maxlength="14"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div id="feedback_cpf" class="text-sm mt-1"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Step 2: Contato -->
                    <div id="step2Content" class="step-content hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Informações de Contato</h4>
                        <div class="space-y-4">
                            <div id="telefones-container">
                                <div class="telefone-item grid grid-cols-1 lg:grid-cols-12 gap-4 mb-4">
                                    <div class="lg:col-span-5">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                        <input type="text" name="telefone_numero[]" placeholder="(11) 99999-9999" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 telefone-input">
                                        <div class="telefone-feedback text-sm mt-1"></div>
                                    </div>
                                    <div class="lg:col-span-5">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                        <input type="text" name="telefone_nome[]" placeholder="Ex: Casa, Trabalho, Celular" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div class="lg:col-span-2 flex items-end justify-end">
                                        <button type="button" onclick="removerTelefone(this)" class="px-3 py-2 text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center">
                                <button type="button" onclick="adicionarTelefone()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Adicionar Telefone
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Step 3: Endereço -->
                    <div id="step3Content" class="step-content hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Endereço</h4>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                                    <input type="text" name="cep" id="wizard_cep" placeholder="00000-000" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                                    <input type="text" name="logradouro" id="wizard_logradouro" placeholder="Rua, Avenida, etc." 
                                           data-address-search="true"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                                    <input type="text" name="numero" id="wizard_numero" placeholder="123" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                                    <input type="text" name="complemento" id="wizard_complemento" placeholder="Apto, Casa, etc." 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                                    <input type="text" name="bairro" id="wizard_bairro" placeholder="Bairro" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                                    <input type="text" name="cidade" id="wizard_cidade" placeholder="São Paulo" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                    <select name="estado" id="wizard_estado" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Selecione...</option>
                                        <option value="SP">São Paulo</option>
                                        <option value="RJ">Rio de Janeiro</option>
                                        <option value="MG">Minas Gerais</option>
                                        <option value="RS">Rio Grande do Sul</option>
                                        <option value="PR">Paraná</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Endereço Completo</label>
                                <div class="text-sm text-gray-500 mb-2">Preencha os campos de endereço acima</div>
                            </div>
                        </div>
                    </div>
                    <!-- Step 4: Pets -->
                    <div id="step4Content" class="step-content hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Pets do Cliente</h4>
                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-700 mb-3">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Você pode adicionar os pets do cliente agora ou depois. Esta etapa é opcional.
                                </p>
                            </div>
                            
                            <div id="pets-container">
                                <!-- Pets serão adicionados aqui dinamicamente -->
                            </div>
                            
                            <div class="flex justify-center">
                                <button type="button" onclick="adicionarPet()" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Adicionar Pet
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Step 5: Confirmação -->
                    <div id="step5Content" class="step-content hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Confirmação dos Dados</h4>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome:</label>
                                    <p id="confirm_nome" class="text-gray-900 font-medium"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">E-mail:</label>
                                    <p id="confirm_email" class="text-gray-900 font-medium"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Telefone:</label>
                                    <p id="confirm_telefone" class="text-gray-900 font-medium"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Endereço:</label>
                                    <p id="confirm_endereco" class="text-gray-900 font-medium"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Pets:</label>
                                    <p id="confirm_pets" class="text-gray-900 font-medium"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Botões de Navegação -->
                    <div class="flex justify-between pt-6 border-t border-gray-200">
                        <button type="button" id="prevBtn" onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 hidden">
                            <i class="fas fa-arrow-left mr-2"></i>Anterior
                        </button>
                        <div class="flex gap-2">
                            <button type="button" id="nextBtn" onclick="nextStep()" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                                Próximo<i class="fas fa-arrow-right ml-2"></i>
                            </button>
                            <button type="submit" id="submitBtn" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 hidden">
                                <i class="fas fa-save mr-2"></i>Salvar Cliente
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function logWizard(msg, data) {
        if (data !== undefined) {
            console.log(`[WIZARD] ${msg}:`, data);
        } else {
            console.log(`[WIZARD] ${msg}`);
        }
    }

    let currentStep = 1;
    const totalSteps = 5;
    let cpfValido = true;
    let telefoneValido = true;
    let cpfValidando = false;
    let telefoneValidando = false;
    let aguardandoAvancoWizard = false;

    function toggleNextBtn() {
        const nextBtn = document.getElementById('nextBtn');
        logWizard('toggleNextBtn', { cpfValido, telefoneValido, cpfValidando, telefoneValidando });
        if (!cpfValido || !telefoneValido || cpfValidando || telefoneValidando) {
            nextBtn.disabled = true;
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
            nextBtn.style.display = 'none';
        } else {
            nextBtn.disabled = false;
            nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            nextBtn.style.display = '';
        }
    }

    function openWizard() {
        const modal = document.getElementById('wizardModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.remove('hidden');
            resetWizard();
        }
    }
    function closeWizard() {
        const modal = document.getElementById('wizardModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            resetWizard();
        }
    }
    function resetWizard() {
        currentStep = 1;
        updateStepDisplay();
        document.getElementById('wizardForm').reset();
        document.getElementById('wizard_id').value = '';
        // Limpar pets container
        document.getElementById('pets-container').innerHTML = '';
    }
    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
            }
        }
    }
    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepDisplay();
        }
    }
    function updateStepDisplay() {
        for (let i = 1; i <= totalSteps; i++) {
            document.getElementById(`step${i}Content`).classList.add('hidden');
            document.getElementById(`step${i}`).classList.remove('bg-blue-500', 'text-white');
            document.getElementById(`step${i}`).classList.add('bg-gray-200', 'text-gray-500');
        }
        document.getElementById(`step${currentStep}Content`).classList.remove('hidden');
        document.getElementById(`step${currentStep}`).classList.remove('bg-gray-200', 'text-gray-500');
        document.getElementById(`step${currentStep}`).classList.add('bg-blue-500', 'text-white');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        if (currentStep === 1) {
            prevBtn.classList.add('hidden');
        } else {
            prevBtn.classList.remove('hidden');
        }
        if (currentStep === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
            updateConfirmation();
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
    }
    function validateCurrentStep() {
        if (currentStep === 1) {
            const nome = document.getElementById('wizard_nome').value.trim();
            if (!nome) {
                alert('Por favor, preencha o nome obrigatório.');
                return false;
            }
        }
        // Step 4 (pets) é opcional, sempre permite avançar
        return true;
    }
    function updateConfirmation() {
        document.getElementById('confirm_nome').textContent = document.getElementById('wizard_nome').value || '-';
        document.getElementById('confirm_email').textContent = document.getElementById('wizard_email').value || '-';
        // Mostrar múltiplos telefones
        const telefonesNomes = document.querySelectorAll('input[name="telefone_nome[]"]');
        const telefonesNumeros = document.querySelectorAll('input[name="telefone_numero[]"]');
        let telefonesText = '';
        for (let i = 0; i < telefonesNomes.length; i++) {
            const nome = telefonesNomes[i].value.trim();
            const numero = telefonesNumeros[i].value.trim();
            if (nome || numero) {
                if (telefonesText) telefonesText += ', ';
                telefonesText += `${nome || 'Sem nome'}: ${formatarTelefoneExibicao(numero) || 'Sem número'}`;
            }
        }
        document.getElementById('confirm_telefone').textContent = telefonesText || '-';
        // Montar endereço detalhado
        const logradouro = document.getElementById('wizard_logradouro').value.trim();
        const numero = document.getElementById('wizard_numero').value.trim();
        const complemento = document.getElementById('wizard_complemento').value.trim();
        const bairro = document.getElementById('wizard_bairro').value.trim();
        const cidade = document.getElementById('wizard_cidade').value.trim();
        const estado = document.getElementById('wizard_estado').value.trim();
        const cep = document.getElementById('wizard_cep').value.trim();
        let partes = [];
        if (logradouro) partes.push(logradouro);
        if (numero) partes.push(numero);
        if (complemento) partes.push(complemento);
        if (bairro) partes.push(bairro);
        if (cidade) partes.push(cidade);
        if (estado) partes.push(estado);
        if (cep) partes.push('CEP: ' + cep);
        let endereco = partes.join(', ');
        document.getElementById('confirm_endereco').textContent = endereco || 'Não informado';
        
        // Mostrar pets
        const petsContainer = document.getElementById('pets-container');
        const petItems = petsContainer.querySelectorAll('.pet-item');
        let petsText = '';
        petItems.forEach((item, index) => {
            const nome = item.querySelector('input[name="pet_nome[]"]').value.trim();
            const especie = item.querySelector('input[name="pet_especie[]"]').value.trim();
            const raca = item.querySelector('input[name="pet_raca[]"]').value.trim();
            const idade = item.querySelector('input[name="pet_idade[]"]').value.trim();
            
            if (nome || especie) {
                if (petsText) petsText += ', ';
                petsText += `${nome || 'Sem nome'} (${especie || 'Sem espécie'})`;
                if (raca) petsText += ` - ${raca}`;
                if (idade) petsText += ` - ${idade} anos`;
            }
        });
        document.getElementById('confirm_pets').textContent = petsText || 'Nenhum pet adicionado';
    }
    function editCliente(cliente) {
        openWizard();
        currentStep = 1;
        updateStepDisplay();
        document.getElementById('wizard_id').value = cliente.id || '';
        document.getElementById('wizard_nome').value = cliente.nome || '';
        document.getElementById('wizard_email').value = cliente.email || '';
        // Aplica máscara ao preencher o CPF
        let cpf = cliente.cpf || '';
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length === 11) {
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }
        document.getElementById('wizard_cpf').value = cpf;
        document.getElementById('wizard_endereco').value = cliente.endereco || '';
        document.getElementById('wizard_cep').value = cliente.cep || '';
        document.getElementById('wizard_logradouro').value = cliente.logradouro || '';
        document.getElementById('wizard_numero').value = cliente.numero || '';
        document.getElementById('wizard_complemento').value = cliente.complemento || '';
        document.getElementById('wizard_bairro').value = cliente.bairro || '';
        document.getElementById('wizard_cidade').value = cliente.cidade || '';
        document.getElementById('wizard_estado').value = cliente.estado || '';
        // Carregar telefones via AJAX
        fetch(`buscar-telefones.php?cliente_id=${cliente.id}`)
            .then(response => response.json())
            .then(telefones => {
                const container = document.getElementById('telefones-container');
                container.innerHTML = '';
                if (telefones.length > 0) {
                    telefones.forEach((telefone) => {
                        // Adiciona um novo campo de telefone
                            adicionarTelefone();
                            const telefonesItems = container.querySelectorAll('.telefone-item');
                            const ultimoItem = telefonesItems[telefonesItems.length - 1];
                            const nomeInput = ultimoItem.querySelector('input[name="telefone_nome[]"]');
                            const numeroInput = ultimoItem.querySelector('input[name="telefone_numero[]"]');
                            if (nomeInput) nomeInput.value = telefone.nome;
                            if (numeroInput) numeroInput.value = telefone.numero;
                    });
                } else {
                    // Se não houver telefones, adiciona um campo vazio
                    adicionarTelefone();
                }
            })
            .catch(error => {
                console.error('Erro ao carregar telefones:', error);
            });
        // Carregar pets via AJAX ao editar cliente
        fetch(`buscar-pets.php?cliente_id=${cliente.id}`)
            .then(response => response.json())
            .then(pets => {
                const container = document.getElementById('pets-container');
                container.innerHTML = '';
                if (pets.length > 0) {
                    pets.forEach((pet) => {
                        adicionarPet();
                        const petItems = container.querySelectorAll('.pet-item');
                        const ultimoItem = petItems[petItems.length - 1];
                        if (ultimoItem) {
                            const nomeInput = ultimoItem.querySelector('input[name="pet_nome[]"]');
                            const especieInput = ultimoItem.querySelector('input[name="pet_especie[]"]');
                            const racaInput = ultimoItem.querySelector('input[name="pet_raca[]"]');
                            const idadeInput = ultimoItem.querySelector('input[name="pet_idade[]"]');
                            if (nomeInput) nomeInput.value = pet.nome;
                            if (especieInput) especieInput.value = pet.especie;
                            if (racaInput) racaInput.value = pet.raca;
                            if (idadeInput) idadeInput.value = pet.idade;
                        }
                    });
                } else {
                    adicionarPet();
                }
            })
            .catch(error => {
                console.error('Erro ao carregar pets:', error);
            });
    }
    document.getElementById('wizardModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeWizard();
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeWizard();
        }
    });
    // Máscara de CPF
    function maskCPF(input) {
        let v = input.value.replace(/\D/g, '');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.value = v;
    }
    document.getElementById('wizard_cpf').addEventListener('input', function() { maskCPF(this); });

    // Validação via AJAX aprimorada
    function validarCampo(campo, valor) {
        cpfValidando = (campo === 'cpf');
        logWizard('Iniciando validação AJAX', { campo, valor });
        toggleNextBtn();
        const cliente_id = document.getElementById('wizard_id').value;
        fetch('validar-campo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `campo=${campo}&valor=${encodeURIComponent(valor)}&cliente_id=${cliente_id}`
        })
        .then(response => response.json())
        .then(data => {
            logWizard('Resposta AJAX', data);
            const input = document.getElementById(`wizard_${campo}`);
            const feedback = document.getElementById(`feedback_${campo}`);
            let mensagemErro = data.mensagem || data.error || '';
            if (data.valido) {
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
                if (feedback) {
                    feedback.textContent = '';
                    feedback.className = 'text-sm mt-1';
                }
                if (campo === 'cpf') cpfValido = true;
            } else {
                input.classList.remove('border-green-500');
                input.classList.add('border-red-500');
                if (feedback) {
                    feedback.textContent = mensagemErro;
                    feedback.className = 'text-sm text-red-600 mt-1';
                }
                logWizard('Erro de validação', { campo, mensagem: mensagemErro });
                if (campo === 'cpf') cpfValido = false;
            }
            cpfValidando = false;
            avancarSeAguardando();
            toggleNextBtn();
        })
        .catch(error => {
            logWizard('Erro AJAX', error);
            cpfValidando = false; 
            toggleNextBtn(); 
        });
    }

    // Validação para telefone principal
    function validarTelefonePrincipal() {
        telefoneValidando = true;
        toggleNextBtn();
        const container = document.getElementById('telefones-container');
        const numeroInputs = container.querySelectorAll('input[name="telefone_numero[]"]');
        const numeroInput = numeroInputs[0];
        const feedback = numeroInput ? numeroInput.parentElement.querySelector('.telefone-feedback') : null;
        if (!numeroInput || !numeroInput.value.trim()) {
            if (numeroInput) {
                numeroInput.classList.remove('border-green-500');
                numeroInput.classList.add('border-red-500');
            }
            if (feedback) {
                feedback.textContent = 'O telefone é obrigatório';
                feedback.className = 'telefone-feedback text-sm text-red-600 mt-1';
            }
            telefoneValido = false;
            telefoneValidando = false;
            toggleNextBtn();
            return;
        }
        const valor = numeroInput.value.trim();
        // Verificar se o telefone principal é igual a algum secundário
        let duplicadoInterno = false;
        for (let i = 1; i < numeroInputs.length; i++) {
            if (valor.replace(/\D/g, '') === numeroInputs[i].value.replace(/\D/g, '')) {
                duplicadoInterno = true;
                break;
            }
        }
        if (duplicadoInterno) {
            numeroInput.classList.remove('border-green-500');
            numeroInput.classList.add('border-red-500');
            if (feedback) {
                feedback.textContent = 'O telefone principal não pode ser igual ao secundário.';
                feedback.className = 'telefone-feedback text-sm text-red-600 mt-1';
            }
            telefoneValido = false;
            telefoneValidando = false;
            toggleNextBtn();
            return;
        }
        const cliente_id = document.getElementById('wizard_id').value;
        logWizard('Iniciando validação AJAX telefone', { valor });
        fetch('validar-campo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `campo=telefone&valor=${encodeURIComponent(valor)}&cliente_id=${cliente_id}`
        })
        .then(response => response.json())
        .then(data => {
            logWizard('Resposta AJAX telefone', data);
            if (data.valido) {
                numeroInput.classList.remove('border-red-500');
                numeroInput.classList.add('border-green-500');
                if (feedback) {
                    feedback.textContent = '';
                    feedback.className = 'telefone-feedback text-sm mt-1';
                }
                telefoneValido = true;
            } else {
                numeroInput.classList.remove('border-green-500');
                numeroInput.classList.add('border-red-500');
                if (feedback) {
                    feedback.textContent = data.mensagem || data.error || '';
                    feedback.className = 'telefone-feedback text-sm text-red-600 mt-1';
                }
                logWizard('Erro de validação telefone', { mensagem: data.mensagem || data.error || '' });
                telefoneValido = false;
            }
            telefoneValidando = false;
            avancarSeAguardando();
            toggleNextBtn();
        })
        .catch(error => { 
            logWizard('Erro AJAX telefone', error);
            telefoneValidando = false; 
            toggleNextBtn(); 
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // CPF
        const cpfInput = document.getElementById('wizard_cpf');
        if (cpfInput) {
            cpfInput.addEventListener('blur', function() {
                if (this.value.trim()) {
                    validarCampo('cpf', this.value.trim());
                }
            });
            cpfInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    validarCampo('cpf', this.value.trim());
                }
            });
        }
        // Telefone principal
        const container = document.getElementById('telefones-container');
        if (container) {
            container.addEventListener('blur', function(e) {
                if (e.target.name === 'telefone_numero[]') {
                    validarTelefonePrincipal();
                }
            }, true);
            // Também validar ao digitar
            container.addEventListener('input', function(e) {
                if (e.target.name === 'telefone_numero[]') {
                    validarTelefonePrincipal();
                }
            });
        }
        // Inicializar estado do botão
        toggleNextBtn();
    });

    // Funções para gerenciar pets
    function adicionarPet() {
        const container = document.getElementById('pets-container');
        const novoPet = document.createElement('div');
        novoPet.className = 'pet-item bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4';
        novoPet.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <h5 class="text-sm font-medium text-gray-700">Pet #${container.children.length + 1}</h5>
                <button type="button" onclick="removerPet(this)" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Pet *</label>
                    <input type="text" name="pet_nome[]" placeholder="Nome do pet" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raça</label>
                    <input type="text" name="pet_raca[]" placeholder="Raça do pet" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cor da Pelagem</label>
                    <input type="text" name="pet_cor_pelagem[]" placeholder="Cor da pelagem" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Espécie</label>
                    <input type="text" name="pet_especie[]" placeholder="Cão, Gato, etc." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Idade</label>
                    <input type="number" name="pet_idade[]" placeholder="Idade em anos" min="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banho medicamentoso</label>
                    <select name="pet_banho_medicamentoso[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Higiênica</label>
                    <select name="pet_higienica[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dentes</label>
                    <select name="pet_dentes[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nariz</label>
                    <select name="pet_nariz[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Franja</label>
                    <select name="pet_franja[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Olhos</label>
                    <select name="pet_olhos[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Retirar mirador</label>
                    <select name="pet_retirar_mirador[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alergias</label>
                    <input type="text" name="pet_alergias[]" placeholder="Descreva alergias do pet" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Perfume</label>
                    <select name="pet_perfume[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observação perfume</label>
                    <input type="text" name="pet_obs_perfume[]" placeholder="Observações sobre perfume" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enfeite</label>
                    <select name="pet_enfeite[]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="pet_observacoes[]" rows="2" placeholder="Anotações sobre o pet" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
        `;
        container.appendChild(novoPet);
    }

    function removerPet(button) {
        const petItem = button.closest('.pet-item');
        petItem.remove();
        
        // Renumerar os pets
        const container = document.getElementById('pets-container');
        const petItems = container.querySelectorAll('.pet-item');
        petItems.forEach((item, index) => {
            const title = item.querySelector('h5');
            if (title) {
                title.textContent = `Pet #${index + 1}`;
            }
        });
    }

    // Após a validação AJAX do CPF ou telefone, se estava aguardando avanço, avança automaticamente
    function avancarSeAguardando() {
        if (aguardandoAvancoWizard && cpfValido && !cpfValidando && telefoneValido && !telefoneValidando) {
            aguardandoAvancoWizard = false;
            nextStep();
        }
    }

    // Funções para gerenciar telefones
    function adicionarTelefone() {
        const container = document.getElementById('telefones-container');
        const novoTelefone = document.createElement('div');
        novoTelefone.className = 'telefone-item grid grid-cols-1 lg:grid-cols-12 gap-4 mb-4';
        novoTelefone.innerHTML = `
            <div class="lg:col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" name="telefone_numero[]" placeholder="(11) 99999-9999" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 telefone-input">
                <div class="telefone-feedback text-sm mt-1"></div>
            </div>
            <div class="lg:col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <input type="text" name="telefone_nome[]" placeholder="Ex: Casa, Trabalho, Celular" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="lg:col-span-2 flex items-end justify-end">
                <button type="button" onclick="removerTelefone(this)" class="px-3 py-2 text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(novoTelefone);
        // Vincular validação ao novo campo
        const numeroInput = novoTelefone.querySelector('input[name="telefone_numero[]"]');
        if (numeroInput) {
            numeroInput.addEventListener('input', validarTelefonePrincipal);
            numeroInput.addEventListener('blur', validarTelefonePrincipal);
        }
    }
    function removerTelefone(btn) {
        const item = btn.closest('.telefone-item');
        if (item) item.remove();
    }

    // Função de máscara de telefone
    function maskTelefone(input) {
        let v = input.value.replace(/\D/g, '');
        v = v.replace(/^0/, '');
        if (v.length > 10) {
            v = v.replace(/(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
        } else if (v.length > 5) {
            v = v.replace(/(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (v.length > 2) {
            v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
        } else {
            v = v.replace(/(\d*)/, '($1');
        }
        input.value = v;
    }
    // Aplicar máscara nos campos já existentes
    function aplicarMascaraTelefoneTodos() {
        document.querySelectorAll('input[name="telefone_numero[]"]').forEach(function(input) {
            input.removeEventListener('input', maskTelefoneHandler);
            input.addEventListener('input', maskTelefoneHandler);
        });
    }
    function maskTelefoneHandler(e) { maskTelefone(e.target); }
    aplicarMascaraTelefoneTodos();
    // Sempre que adicionar telefone, aplicar máscara
    const oldAdicionarTelefone = window.adicionarTelefone;
    window.adicionarTelefone = function() {
        if (typeof oldAdicionarTelefone === 'function') oldAdicionarTelefone();
        aplicarMascaraTelefoneTodos();
    };

    // Na confirmação, exibir telefone com máscara
    function formatarTelefoneExibicao(numero) {
        let v = numero.replace(/\D/g, '');
        if (v.length === 11) {
            return v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (v.length === 10) {
            return v.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
            return numero;
        }
    }

    // Função para buscar endereço pelo CEP usando ViaCEP
    function buscarEnderecoPorCEP(cep) {
        cep = cep.replace(/\D/g, '');
        if (cep.length !== 8) return;
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('wizard_logradouro').value = data.logradouro || '';
                    document.getElementById('wizard_bairro').value = data.bairro || '';
                    document.getElementById('wizard_cidade').value = data.localidade || '';
                    document.getElementById('wizard_estado').value = data.uf || '';
                }
            });
    }
    // Vincular evento ao campo CEP
        const cepInput = document.getElementById('wizard_cep');
        if (cepInput) {
            cepInput.addEventListener('blur', function() {
            buscarEnderecoPorCEP(this.value);
        });
        cepInput.addEventListener('input', function() {
            if (this.value.replace(/\D/g, '').length === 8) {
                buscarEnderecoPorCEP(this.value);
            }
        });
    }
    
    // Função para configurar navegação com Enter
    function setupEnterNavigation() {
        const wizardForm = document.getElementById('wizardForm');
        if (!wizardForm) return;
        
        const inputs = wizardForm.querySelectorAll('input, textarea, select');
        
        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    // Se for o último campo do step atual, avançar para o próximo step
                    const currentStepContent = document.getElementById(`step${currentStep}Content`);
                    if (currentStepContent) {
                        const currentStepInputs = currentStepContent.querySelectorAll('input, textarea, select');
                        const isLastInStep = Array.from(currentStepInputs).indexOf(input) === currentStepInputs.length - 1;
                        
                        if (isLastInStep && currentStep < totalSteps) {
                            nextStep();
                        } else if (isLastInStep && currentStep === totalSteps) {
                            // Se for o último campo do último step, submeter o formulário
                            wizardForm.dispatchEvent(new Event('submit'));
                        } else {
                            // Avançar para o próximo campo
                            const nextInput = inputs[index + 1];
                            if (nextInput) {
                                nextInput.focus();
                            }
                        }
                    }
                }
            });
        });
    }
    
    // Função para focar no primeiro campo do step atual
    function focusFirstField() {
        const currentStepContent = document.getElementById(`step${currentStep}Content`);
        if (currentStepContent) {
            const firstInput = currentStepContent.querySelector('input, textarea, select');
            if (firstInput) {
                firstInput.focus();
            }
        }
    }
    
    // Configurar navegação com Enter quando a página carregar
    document.addEventListener('DOMContentLoaded', function() {
        setupEnterNavigation();
        
        // Focar no primeiro campo automaticamente
        const firstInput = document.querySelector('#wizardForm input[autofocus]');
        if (firstInput) {
            firstInput.focus();
        }
    });
    
    // Atualizar a função updateStepDisplay para focar no primeiro campo
    const originalUpdateStepDisplay = updateStepDisplay;
    updateStepDisplay = function() {
        originalUpdateStepDisplay();
        // Focar no primeiro campo após mudar de step
        setTimeout(focusFirstField, 100);
    };
    </script>
<?php
}
include 'layout.php';