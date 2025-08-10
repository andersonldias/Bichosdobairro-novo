<?php
require_once '../src/init.php';

// Verificar se é uma requisição AJAX
if (isAjax()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'buscar':
            $termo = $_GET['termo'] ?? '';
            $pets = Pet::buscar($termo, ['limite' => 10]);
            jsonResponse($pets);
            break;
            
        case 'criar':
            $dados = [
                'nome' => sanitize($_POST['nome'] ?? ''),
                'especie' => sanitize($_POST['especie'] ?? ''),
                'raca' => sanitize($_POST['raca'] ?? ''),
                'idade' => $_POST['idade'] ?? null,
                'peso' => $_POST['peso'] ?? null,
                'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
                'observacoes' => sanitize($_POST['observacoes'] ?? '')
            ];
            
            if (empty($dados['nome']) || empty($dados['especie']) || empty($dados['cliente_id'])) {
                jsonResponse(['success' => false, 'message' => 'Nome, espécie e cliente são obrigatórios'], 400);
            }
            
            $id = Pet::criar($dados);
            if ($id) {
                jsonResponse(['success' => true, 'id' => $id, 'message' => 'Pet criado com sucesso']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Erro ao criar pet'], 500);
            }
            break;
            
        default:
            jsonResponse(['error' => 'Ação não reconhecida'], 400);
    }
}

$todos = Pet::listarTodos();

// Mensagens de feedback
$msg = '';
$erro = '';

// Edição: buscar pet se id_edit estiver presente
$editando = false;
$pet_edit = [
    'id' => '', 'nome' => '', 'especie' => '', 'raca' => '', 'idade' => '', 'cliente_id' => ''
];
if (isset($_GET['id_edit'])) {
    $id_edit = $_GET['id_edit'];
    $todos = Pet::listarTodos();
    foreach ($todos as $p) {
        if ($p['id'] == $id_edit) {
            $pet_edit = $p;
            $editando = true;
            break;
        }
    }
}

// Cadastro ou edição de pet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['especie'], $_POST['cliente_id'])) {
    try {
        if (isset($_POST['id']) && $_POST['id']) {
            Pet::atualizar($_POST['id'], $_POST['nome'], $_POST['especie'], $_POST['raca'] ?? '', $_POST['idade'] ?? '', $_POST['cliente_id']);
            $msg = 'Pet atualizado com sucesso!';
        } else {
            Pet::criar($_POST['nome'], $_POST['especie'], $_POST['raca'] ?? '', $_POST['idade'] ?? '', $_POST['cliente_id']);
            $msg = 'Pet cadastrado com sucesso!';
        }
        header('Location: pets.php?msg=' . urlencode($msg));
        exit;
    } catch (Exception $e) {
        $erro = 'Erro ao salvar: ' . $e->getMessage();
        header('Location: pets.php?erro=' . urlencode($erro));
        exit;
    }
}

// Exclusão de pet
if (isset($_GET['excluir'])) {
    try {
        Pet::deletar($_GET['excluir']);
        $msg = 'Pet excluído com sucesso!';
    } catch (Exception $e) {
        $erro = 'Erro ao excluir: ' . $e->getMessage();
    }
}

// Busca e filtros
$busca = $_GET['busca'] ?? '';
$ordenacao = $_GET['ordenacao'] ?? 'nome';
$direcao = $_GET['direcao'] ?? 'asc';

// Listar pets e clientes
$pets = Pet::listarTodos();
$clientes = Cliente::listarTodos();

// Aplicar filtros
if ($busca) {
    $pets = array_filter($pets, function($p) use ($busca) {
        return stripos($p['nome'], $busca) !== false || 
               stripos($p['especie'], $busca) !== false ||
               stripos($p['raca'], $busca) !== false ||
               stripos($p['cliente_nome'] ?? '', $busca) !== false;
    });
}

// Aplicar ordenação
usort($pets, function($a, $b) use ($ordenacao, $direcao) {
    $valor_a = $a[$ordenacao] ?? '';
    $valor_b = $b[$ordenacao] ?? '';
    
    if ($direcao === 'asc') {
        return strcasecmp($valor_a, $valor_b);
    } else {
        return strcasecmp($valor_b, $valor_a);
    }
});

// Estatísticas
$total_pets = count($pets);
$especies_unicas = array_unique(array_column($pets, 'especie'));
$total_especies = count($especies_unicas);
$pets_com_idade = count(array_filter($pets, function($p) { return !empty($p['idade']); }));

// Agrupar pets por cliente
$pets_por_cliente = [];
foreach ($clientes as $cli) {
    $pets_cli = Pet::buscarPorCliente($cli['id']);
    if (!empty($pets_cli)) {
        $pets_por_cliente[$cli['id']] = $pets_cli;
    }
}

// Exibir mensagem via GET se houver
$msg = $_GET['msg'] ?? $msg;
$erro = $_GET['erro'] ?? $erro;

function render_content() {
    global $msg, $erro, $pets, $clientes, $editando, $pet_edit;
    global $busca, $ordenacao, $direcao;
    global $total_pets, $total_especies, $pets_com_idade;
    global $pets_por_cliente;
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
                    <div class="p-2 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-dog text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total de Pets</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $total_pets ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-tags text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Espécies</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $total_especies ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-birthday-cake text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Com Idade</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $pets_com_idade ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campo de Busca e Botão Adicionar Pet -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-2">
            <form method="get" class="w-full md:w-auto flex-1">
                <div class="relative w-full max-w-md">
                    <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar pets..." class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <button onclick="openPetModal()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center gap-2">
                <i class="fas fa-plus"></i> Adicionar Pet
            </button>
        </div>

        <!-- Lista de Pets Agrupada por Cliente -->
        <div class="space-y-8">
        <?php
        $tem_pets = false;
        foreach ($clientes as $cli) {
            if (!empty($pets_por_cliente[$cli['id']])) {
                $tem_pets = true;
                break;
            }
        }
        if (!$tem_pets): ?>
            <div class="text-center text-gray-400 text-lg py-16">Nenhum pet cadastrado.</div>
        <?php endif; ?>
        <?php foreach ($clientes as $cli): ?>
            <?php if (empty($pets_por_cliente[$cli['id']])) continue; ?>
            <div class="bg-white rounded-xl shadow p-0 border border-gray-100">
                <div class="flex items-center justify-between px-6 pt-5 pb-2 border-b border-gray-100">
                    <div>
                        <div class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-user text-blue-600"></i> <?= htmlspecialchars($cli['nome']) ?>
                        </div>
                        <div class="text-sm text-gray-600 mt-1 flex items-center gap-2">
                            <i class="fas fa-phone text-gray-400"></i>
                            <?php 
                            $tels = Cliente::buscarTelefones($cli['id']);
                            echo isset($tels[0]['numero']) ? htmlspecialchars($tels[0]['numero']) : '-';
                            ?>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 font-medium">
                        <?= count($pets_por_cliente[$cli['id']]) ?> pet<?= count($pets_por_cliente[$cli['id']]) > 1 ? 's' : '' ?>
                    </div>
                </div>
                <div class="px-6 pb-5 pt-4">
                    <div class="flex flex-wrap gap-4">
                        <?php foreach ($pets_por_cliente[$cli['id']] as $pet): ?>
                            <div class="bg-white border border-gray-200 rounded-lg p-4 min-w-[220px] flex flex-col justify-between relative shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-semibold text-gray-800 flex items-center gap-1">
                                        <i class="fas fa-paw text-green-500"></i> <?= htmlspecialchars($pet['nome']) ?>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="editPet(<?= htmlspecialchars(json_encode($pet)) ?>)" class="text-blue-500 hover:text-blue-700" title="Editar"><i class="fas fa-pen-to-square"></i></button>
                                        <a href="pets.php?excluir=<?= $pet['id'] ?>" onclick="return confirm('Excluir este pet?')" class="text-red-500 hover:text-red-700" title="Excluir"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-600 mb-1"><span class="font-medium">Espécie:</span> <?= htmlspecialchars($pet['especie']) ?></div>
                                <div class="text-sm text-gray-600"><span class="font-medium">Raça:</span> <?= htmlspecialchars($pet['raca']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal para Cadastro/Edição de Pet -->
    <div id="petModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="flex items-center justify-center min-h-screen w-full">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl min-w-[500px] md:min-w-[700px] p-10 md:p-12 border border-gray-200 max-h-[90vh] overflow-y-auto mx-auto">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Novo Pet</h3>
                        <button onclick="closePetModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <form id="petForm" method="post" class="space-y-4 p-0">
                        <input type="hidden" name="id" id="pet_id">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tutor *</label>
                            <select name="cliente_id" id="pet_cliente_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione o tutor</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Pet *</label>
                                <input type="text" name="nome" id="pet_nome" placeholder="Nome do pet" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Raça</label>
                                <input type="text" name="raca" id="pet_raca" placeholder="Raça do pet" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cor da Pelagem</label>
                                <input type="text" name="cor_pelagem" id="pet_cor_pelagem" placeholder="Cor da pelagem" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Espécie</label>
                                <input type="text" name="especie" id="pet_especie" placeholder="Cão, Gato, etc."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Idade</label>
                                <input type="number" name="idade" id="pet_idade" placeholder="Idade em anos" min="0" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Banho medicamentoso</label>
                                <select name="banho_medicamentoso" id="pet_banho_medicamentoso" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Higiênica</label>
                                <select name="higienica" id="pet_higienica" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dentes</label>
                                <select name="dentes" id="pet_dentes" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nariz</label>
                                <select name="nariz" id="pet_nariz" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Franja</label>
                                <select name="franja" id="pet_franja" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Olhos</label>
                                <select name="olhos" id="pet_olhos" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Retirar mirador</label>
                                <select name="retirar_mirador" id="pet_retirar_mirador" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alergias</label>
                                <input type="text" name="alergias" id="pet_alergias" placeholder="Descreva alergias do pet" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Perfume</label>
                                <select name="perfume" id="pet_perfume" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observação perfume</label>
                                <input type="text" name="obs_perfume" id="pet_obs_perfume" placeholder="Observações sobre perfume" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Enfeite</label>
                                <select name="enfeite" id="pet_enfeite" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Selecione</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-4">
                            <button type="submit" class="flex-1 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                <i class="fas fa-save mr-2"></i>Salvar
                            </button>
                            <button type="button" onclick="closePetModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                        </div>
                    </form>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" id="pet_observacoes" rows="2" placeholder="Anotações sobre o pet" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPetModal() {
            document.getElementById('petModal').style.display = 'flex';
            document.getElementById('modalTitle').textContent = 'Novo Pet';
            document.getElementById('petForm').reset();
            document.getElementById('pet_id').value = '';
        }

        function closePetModal() {
            document.getElementById('petModal').style.display = 'none';
        }

        function editPet(pet) {
            document.getElementById('petModal').style.display = 'flex';
            document.getElementById('modalTitle').textContent = 'Editar Pet';
            document.getElementById('pet_id').value = pet.id;
            document.getElementById('pet_nome').value = pet.nome;
            document.getElementById('pet_especie').value = pet.especie;
            document.getElementById('pet_raca').value = pet.raca;
            document.getElementById('pet_idade').value = pet.idade;
            document.getElementById('pet_cliente_id').value = pet.cliente_id;
        }

        // Fechar modal ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePetModal();
            }
        });
    </script>
<?php
}
include 'layout.php'; 