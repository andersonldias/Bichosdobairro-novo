<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../src/init.php';

// Endpoint para salvar agendamento via AJAX deve vir antes de qualquer saída HTML!
if (isset($_GET['action']) && $_GET['action'] === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log: conteúdo do POST (sempre, mesmo se vazio)
    // Debug removido para produção
    // Log: chamada do endpoint
    file_put_contents(__DIR__ . '/../logs/debug_salvar.txt', date('c') . " - Chamou endpoint salvar\n", FILE_APPEND);
    // Log: variáveis de ambiente e servidor
    // Debug removido para produção
    $debug = [];
    $debug['POST'] = $_POST;
    $id = $_POST['id'] ?? '';
    $pet_id = $_POST['pet_id'] ?? '';
    $cliente_id = $_POST['cliente_id'] ?? '';
    $data = $_POST['data'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $servico = $_POST['servico'] ?? '';
    $status = $_POST['status'] ?? 'Pendente';
    $observacoes = $_POST['observacoes'] ?? '';
    $debug['valores'] = compact('id','pet_id','cliente_id','data','hora','servico','status','observacoes');
    try {
        if ($id) {
            $debug['acao'] = 'atualizar';
            $resultado = Agendamento::atualizar($id, $pet_id, $cliente_id, $data, $hora, $servico, $status, $observacoes);
            $debug['resultado_funcao'] = $resultado;
        } else {
            $debug['acao'] = 'criar';
            $dados = [
                'cliente_id' => $cliente_id,
                'pet_id' => $pet_id,
                'data' => $data,
                'hora' => $hora,
                'servico' => $servico,
                'observacoes' => $observacoes,
                'status' => $status
            ];
            $debug['dados_para_criar'] = $dados;
            $resultado = Agendamento::criar($dados);
            $debug['resultado_funcao'] = $resultado;
        }
        $debug['resultado'] = 'ok';
        // Debug removido para produção
        echo 'ok';
    } catch (Exception $e) {
        $debug['erro'] = $e->getMessage();
        $debug['trace'] = $e->getTraceAsString();
        // Debug removido para produção
        file_put_contents(__DIR__ . '/../logs/debug_salvar.txt', date('c') . " - Erro: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
        http_response_code(500);
        // Exibir erro detalhado para depuração
        echo '<h1>Erro Interno do Servidor</h1>';
        echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        exit;
    }
    exit;
}

// Endpoints AJAX devem vir antes de qualquer saída HTML!
if (isset($_GET['action']) && $_GET['action'] === 'buscar_clientes') {
    $termo = $_GET['q'] ?? '';
    $clientes = Cliente::buscarPorNome($termo);
    $resultados = [];
    foreach ($clientes as $c) {
        $resultados[] = [
            'id' => $c['id'],
            'text' => $c['nome']
        ];
    }
    header('Content-Type: application/json');
    echo json_encode(['results' => $resultados]);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'buscar_pets') {
    $cliente_id = intval($_GET['cliente_id'] ?? 0);
            $pets = Pet::buscarPorCliente($cliente_id);
    $resultados = [];
    foreach ($pets as $p) {
        $resultados[] = [
            'id' => $p['id'],
            'text' => $p['nome']
        ];
    }
    header('Content-Type: application/json');
    echo json_encode(['results' => $resultados]);
    exit;
}

// Endpoint para o FullCalendar: retorna agendamentos em JSON (incluindo recorrentes)
if (isset($_GET['action']) && $_GET['action'] === 'listar') {
    try {
        // Obter período do calendário (últimos 30 dias até próximos 90 dias)
        $dataInicio = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
        $dataFim = $_GET['end'] ?? date('Y-m-d', strtotime('+90 days'));
        
        header('Content-Type: application/json');
        $eventos = [];
        
        // Buscar agendamentos para o período específico (incluindo recorrentes)
        $pdo = getDb();
        $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome,
                       ar.tipo_recorrencia, ar.dia_semana, ar.semana_mes
                FROM agendamentos a
                JOIN clientes c ON a.cliente_id = c.id
                JOIN pets p ON a.pet_id = p.id
                LEFT JOIN agendamentos_recorrentes ar ON a.recorrencia_id = ar.id
                WHERE a.data BETWEEN :data_inicio AND :data_fim
                ORDER BY a.data, a.hora";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
        
        $agendamentos = $stmt->fetchAll();
        
        // Gerar ocorrências de agendamentos recorrentes para o período
        if (class_exists('AgendamentoRecorrente')) {
            $ocorrencias = AgendamentoRecorrente::gerarOcorrencias($dataInicio, $dataFim);
            
            // Criar agendamentos para as ocorrências que ainda não existem
            foreach ($ocorrencias as $ocorrencia) {
                $existe = AgendamentoRecorrente::verificarAgendamentoExistente($ocorrencia['recorrencia_id'], $ocorrencia['data']);
                if (!$existe) {
                    AgendamentoRecorrente::criarAgendamentoOcorrencia($ocorrencia);
                }
            }
            
            // Buscar novamente para incluir as novas ocorrências
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            $agendamentos = $stmt->fetchAll();
        }
        
        // Processar todos os agendamentos (normais e recorrentes)
        foreach ($agendamentos as $a) {
            $eventos[] = [
                'id' => $a['id'],
                'title' => $a['pet_nome'] . ' - ' . $a['servico'],
                'start' => $a['data'] . 'T' . $a['hora'],
                'end' => $a['data'] . 'T' . $a['hora'],
                'backgroundColor' => $a['recorrencia_id'] ? '#3b82f6' : '#10b981', // Azul para recorrentes, verde para normais
                'borderColor' => $a['recorrencia_id'] ? '#2563eb' : '#059669',
                'extendedProps' => [
                    'pet_id' => $a['pet_id'],
                    'cliente_id' => $a['cliente_id'],
                    'servico' => $a['servico'],
                    'status' => $a['status'],
                    'observacoes' => $a['observacoes'],
                    'pet_nome' => $a['pet_nome'],
                    'cliente_nome' => $a['cliente_nome'],
                    'recorrencia_id' => $a['recorrencia_id'] ?? null,
                    'tipo_recorrencia' => $a['tipo_recorrencia'] ?? null,
                    'is_recorrente' => !empty($a['recorrencia_id'])
                ]
            ];
        }
        
        echo json_encode($eventos);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

require_once '../src/init.php';

// Verificar se é uma requisição AJAX
if (isAjax()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'buscar':
            $termo = $_GET['termo'] ?? '';
            $agendamentos = Agendamento::buscar($termo, ['limite' => 10]);
            jsonResponse($agendamentos);
            break;
            
        case 'criar':
            $dados = [
                'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
                'pet_id' => (int)($_POST['pet_id'] ?? 0),
                'data' => $_POST['data'] ?? '',
                'hora' => $_POST['hora'] ?? '',
                'servico' => sanitize($_POST['servico'] ?? ''),
                'observacoes' => sanitize($_POST['observacoes'] ?? ''),
                'status' => 'agendado'
            ];
            
            if (empty($dados['cliente_id']) || empty($dados['data']) || empty($dados['hora'])) {
                jsonResponse(['success' => false, 'message' => 'Cliente, data e hora são obrigatórios'], 400);
            }
            
            $id = Agendamento::criar($dados);
            if ($id) {
                jsonResponse(['success' => true, 'id' => $id, 'message' => 'Agendamento criado com sucesso']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Erro ao criar agendamento'], 500);
            }
            break;
            
        default:
            jsonResponse(['error' => 'Ação não reconhecida'], 400);
    }
}

// Modal de Agendamento sempre presente
?>
<div id="agendaModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
    <div class="flex items-center justify-center min-h-screen w-full">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-8 border border-gray-200 max-h-[90vh] overflow-y-auto mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-800" id="agendaModalTitle">Novo Agendamento</h3>
                <button onclick="closeAgendaModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="agendaForm" method="post" class="space-y-4">
                <input type="hidden" name="id" id="agenda_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                        <select name="cliente_id" id="agenda_cliente_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md"></select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pet *</label>
                        <select name="pet_id" id="agenda_pet_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">Selecione o pet</option>
                            <?php if (isset($pets) && is_array($pets)) foreach ($pets as $p): ?>
                                <option value="<?= $p['id'] ?>" data-cliente="<?= $p['cliente_id'] ?>" data-nome="<?= htmlspecialchars($p['nome']) ?>"><?= htmlspecialchars($p['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                        <input type="date" name="data" id="agenda_data" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Horário *</label>
                        <input type="time" name="hora" id="agenda_hora" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Serviço *</label>
                        <input type="text" name="servico" id="agenda_servico" required class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Ex: Banho, Tosa, Consulta...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="agenda_status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="Pendente">Pendente</option>
                            <option value="Concluído">Concluído</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" id="agenda_observacoes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>
                </div>
                <div class="flex gap-2 pt-4">
                    <button type="submit" class="flex-1 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i>Salvar
                    </button>
                    <button type="button" onclick="closeAgendaModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$todos = Agendamento::listarTodos();

function render_content() {
    global $msg, $erro, $agendamentos, $pets, $clientes, $editando, $agendamento_edit;
    
    // Tratamento de exclusão de agendamento
    if (isset($_GET['excluir']) && isset($_GET['dia'])) {
        $idExcluir = intval($_GET['excluir']);
        if (Agendamento::deletar($idExcluir)) {
            $msg = 'Agendamento excluído com sucesso!';
        } else {
            $erro = 'Erro ao excluir agendamento.';
        }
        // Redireciona para evitar reenvio do parâmetro
        header('Location: agendamentos.php?dia=' . urlencode($_GET['dia']) . ($msg ? '&msg=' . urlencode($msg) : '') . ($erro ? '&erro=' . urlencode($erro) : ''));
        exit;
    }
    
    if (isset($_GET['dia'])) {
        $data = $_GET['dia'];
        $dia_semana = date('w', strtotime($data));
        $config_file = __DIR__ . '/../config_agenda.json';
        $config = [
            'inicio' => '08:00',
            'fim' => '18:00',
            'intervalo' => 20,
            'abertos' => [1,1,1,1,1,1,1],
            'intervalos' => [20,20,20,20,20,20,20]
        ];
        if (file_exists($config_file)) {
            $json = file_get_contents($config_file);
            $data_config = json_decode($json, true);
            if (is_array($data_config)) {
                $config = array_merge($config, $data_config);
            }
        }
        $dia_aberto = isset($config['abertos'][$dia_semana]) ? $config['abertos'][$dia_semana] : 1;
        $intervalo_dia = isset($config['intervalos'][$dia_semana]) ? $config['intervalos'][$dia_semana] : $config['intervalo'];
        
        // Buscar agendamentos incluindo recorrentes para o dia específico
        $pdo = getDb();
        $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome, ar.tipo_recorrencia
                FROM agendamentos a
                JOIN clientes c ON a.cliente_id = c.id
                JOIN pets p ON a.pet_id = p.id
                LEFT JOIN agendamentos_recorrentes ar ON a.recorrencia_id = ar.id
                WHERE a.data = :data
                ORDER BY a.hora";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['data' => $data]);
        $agendamentos = $stmt->fetchAll();
        
        // Gerar ocorrências de agendamentos recorrentes para este dia se necessário
        if (class_exists('AgendamentoRecorrente')) {
            $ocorrencias = AgendamentoRecorrente::gerarOcorrencias($data, $data);
            
            // Criar agendamentos para as ocorrências que ainda não existem
            foreach ($ocorrencias as $ocorrencia) {
                $existe = AgendamentoRecorrente::verificarAgendamentoExistente($ocorrencia['recorrencia_id'], $ocorrencia['data']);
                if (!$existe) {
                    AgendamentoRecorrente::criarAgendamentoOcorrencia($ocorrencia);
                }
            }
            
            // Buscar novamente para incluir as novas ocorrências
            $stmt->execute(['data' => $data]);
            $agendamentos = $stmt->fetchAll();
        }
        $horariosOcupados = array_map(function($a) { return trim(substr($a['hora'],0,5)); }, $agendamentos);
        $lista = [];
        if ($dia_aberto) {
            $inicio = $config['inicio'];
            $fim = $config['fim'];
            $intervalo = intval($intervalo_dia);
            $horaAtual = $inicio;
            while ($horaAtual < $fim) {
                $lista[] = $horaAtual;
                list($h, $m) = explode(':', $horaAtual);
                $m += $intervalo;
                while ($m >= 60) { $h++; $m -= 60; }
                $horaAtual = (strlen($h)<2?'0':'').$h . ':' . (strlen($m)<2?'0':'').$m;
            }
        }
        ?>
        <div class="max-w-xl mx-auto mt-8 space-y-6">
            <button onclick="window.location='agendamentos.php'" class="mb-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Voltar para o mês</button>
            <h2 class="text-2xl font-bold mb-4">Agendamentos do dia <?= date('d/m/Y', strtotime($data)) ?></h2>
            <div class="bg-white rounded-lg shadow p-6">
                <?php if ($dia_aberto): ?>
                    <ul class="divide-y">
                        <?php foreach ($lista as $horario): ?>
                            <?php $ocupado = in_array(trim($horario), $horariosOcupados); ?>
                            <li class="py-2 flex items-center justify-between <?= $ocupado ? '' : 'cursor-pointer hover:bg-blue-50 bg-green-50' ?>" <?= $ocupado ? '' : "onclick=\"abrirNovoAgendamento('$data','$horario')\" data-debug='livre'" ?> >
                                <div class="flex items-center gap-3">
                                    <span class="<?= $ocupado ? 'text-gray-400' : 'text-gray-800' ?> font-mono w-14 text-right"><?= $horario ?></span>
                                    <?php if ($ocupado): ?>
                                        <?php 
                                        foreach ($agendamentos as $a) {
                                            if (trim(substr($a['hora'],0,5)) === trim($horario)) {
                                                $primeiro_nome = explode(' ', $a['cliente_nome'])[0];
                                                $isRecorrente = !empty($a['recorrencia_id']);
                                                $bgColor = $isRecorrente ? 'bg-blue-200' : 'bg-blue-100';
                                                $textColor = $isRecorrente ? 'text-blue-800' : 'text-blue-900';
                                                $icon = $isRecorrente ? 'fas fa-redo' : 'fas fa-calendar';
                                                
                                                echo '<span class="flex items-center gap-2 ' . $bgColor . ' ' . $textColor . ' rounded px-3 py-1 shadow-sm font-semibold text-sm">';
                                                echo '<i class="' . $icon . '"></i> ' . htmlspecialchars($primeiro_nome);
                                                echo ' <i class="fas fa-paw"></i> ' . htmlspecialchars($a['pet_nome']);
                                                echo ' <i class="fas fa-cut"></i> ' . htmlspecialchars($a['servico']);
                                                if ($isRecorrente) {
                                                    echo ' <span class="text-xs bg-blue-300 px-1 rounded">REC</span>';
                                                }
                                                echo '</span>';
                                            }
                                        }
                                        ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($ocupado): ?>
                                <div class="flex gap-1 items-center">
                                    <?php 
                                    foreach ($agendamentos as $a) {
                                        if (trim(substr($a['hora'],0,5)) === trim($horario)) {
                                            // Botão editar com data-agendamento
                                            echo ' <button type="button" class="text-blue-500 hover:text-blue-700" title="Editar" data-agendamento="' . htmlspecialchars(json_encode($a), ENT_QUOTES, 'UTF-8') . '" onclick="editarAgendamento(this)"><i class="fas fa-pen"></i></button>';
                                            // Botão deletar
                                            echo ' <a href="agendamentos.php?dia=' . urlencode($data) . '&excluir=' . $a['id'] . '" onclick="return confirm(\'Excluir este agendamento?\')" class="text-red-500 hover:text-red-700 ml-1" title="Excluir"><i class="fas fa-trash"></i></a>';
                                        }
                                    }
                                    ?>
                                </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <script>
        function openAgendaModal() {
            document.getElementById('agendaModal').style.display = 'flex';
            document.getElementById('agendaModalTitle').textContent = 'Novo Agendamento';
            document.getElementById('agendaForm').reset();
            document.getElementById('agenda_id').value = '';
        }
        function closeAgendaModal() {
            document.getElementById('agendaModal').style.display = 'none';
        }
        function abrirNovoAgendamento(data, hora) {
            openAgendaModal();
            document.getElementById('agenda_data').value = data;
            document.getElementById('agenda_hora').value = hora;
        }
        function editarAgendamento(btn) {
            var agendamento = btn.dataset.agendamento ? JSON.parse(btn.dataset.agendamento) : null;
            if (!agendamento) return;
            openAgendaModal();
            document.getElementById('agenda_id').value = agendamento.id || '';
            document.getElementById('agenda_data').value = agendamento.data || '';
            document.getElementById('agenda_hora').value = agendamento.hora ? agendamento.hora.substring(0,5) : '';
            document.getElementById('agenda_servico').value = agendamento.servico || '';
            document.getElementById('agenda_status').value = agendamento.status || 'Pendente';
            document.getElementById('agenda_observacoes').value = agendamento.observacoes || '';

            // Função para setar valor do select, aguardando opções
            function setSelectValue(selectId, value, callback) {
                var select = document.getElementById(selectId);
                if (!select) return;
                if ([...select.options].some(opt => opt.value == value)) {
                    select.value = value;
                    if (typeof callback === 'function') callback();
                } else {
                    setTimeout(function() { setSelectValue(selectId, value, callback); }, 100);
                }
            }

            // Função para popular clientes via AJAX se necessário
            function popularClientes(callback) {
                var clienteSelect = document.getElementById('agenda_cliente_id');
                if (!clienteSelect) return;
                if (clienteSelect.options.length > 0) {
                    if (typeof callback === 'function') callback();
                    return;
                }
                // Buscar clientes via AJAX
                fetch('agendamentos.php?action=buscar_clientes&q=')
                    .then(r => r.json())
                    .then(data => {
                        clienteSelect.innerHTML = '';
                        var opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = 'Selecione o cliente';
                        clienteSelect.appendChild(opt);
                        if (data.results) {
                            data.results.forEach(function(cli) {
                                var option = document.createElement('option');
                                option.value = cli.id;
                                option.textContent = cli.text;
                                clienteSelect.appendChild(option);
                            });
                        }
                        if (typeof callback === 'function') callback();
                    });
            }

            // Fluxo: popular clientes -> setar cliente -> disparar change -> setar pet
            popularClientes(function() {
                setSelectValue('agenda_cliente_id', agendamento.cliente_id, function() {
                    var clienteSelect = document.getElementById('agenda_cliente_id');
                    if (clienteSelect) {
                        var event = new Event('change', { bubbles: true });
                        clienteSelect.dispatchEvent(event);
                    }
                    setSelectValue('agenda_pet_id', agendamento.pet_id);
                });
            });

            // Preencher status, adicionando a opção se necessário
            var statusSelect = document.getElementById('agenda_status');
            if (statusSelect && agendamento.status) {
                var found = false;
                for (var i = 0; i < statusSelect.options.length; i++) {
                    if (statusSelect.options[i].value == agendamento.status) {
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    var opt = document.createElement('option');
                    opt.value = agendamento.status;
                    opt.textContent = agendamento.status;
                    statusSelect.appendChild(opt);
                }
                statusSelect.value = agendamento.status;
            } else if (statusSelect) {
                statusSelect.value = 'Pendente';
            }

            document.getElementById('agendaModalTitle').textContent = 'Editar Agendamento';
        }
        </script>
        <?php
        return;
    }
    ?>
    <div class="space-y-6">
        <!-- FullCalendar CSS/JS -->
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
        
        <!-- Container do calendário -->
        <div class="flex justify-center">
            <div class="relative w-full max-w-2xl">
                <!-- Botão de ação principal -->
        <div class="flex gap-4 mb-6">
            <button onclick="openAgendaModal()" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Novo Agendamento
            </button>
        </div>
        
        <div id="calendar" class="mb-8"></div>
                <!-- Modal seletor de anos -->
                <div id="yearSelectorModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999;" class="flex items-center justify-center">
                    <div style="background:white; border-radius:12px; padding:32px; max-width:340px; margin:80px auto; box-shadow:0 4px 24px rgba(0,0,0,0.15);">
                        <h3 class="text-lg font-bold mb-4 text-center">Escolha o ano</h3>
                        <div id="yearGrid" class="grid grid-cols-3 gap-4"></div>
                        <button onclick="document.getElementById('yearSelectorModal').style.display='none'" class="mt-6 w-full bg-gray-200 hover:bg-gray-300 text-gray-700 rounded px-4 py-2">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

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

        <!-- Adicionar modal HTML e função abrirModalAgendamentosDia: -->
        <div id="modalAgendamentosDia" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999;" class="flex items-center justify-center">
            <div style="background:white; border-radius:12px; padding:32px; max-width:500px; margin:80px auto; box-shadow:0 4px 24px rgba(0,0,0,0.15); min-width:320px;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold" id="modalAgendamentosDiaTitulo">Agendamentos do dia</h3>
                    <button onclick="document.getElementById('modalAgendamentosDia').style.display='none'" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
                </div>
                <div id="modalAgendamentosDiaConteudo">Carregando...</div>
            </div>
        </div>

        <script>
        // Fechar modal ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAgendaModal();
            }
        });

        // Inicialização do FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            if (!calendarEl) {
                console.error('Elemento calendar não encontrado');
                return;
            }
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'timeGridDay,dayGridMonth,yearBtn'
                },
                customButtons: {
                    yearBtn: {
                        text: 'Ano',
                        click: function() {
                            // Abrir modal seletor de anos (grade 3x3, ano atual centralizado)
                            var modal = document.getElementById('yearSelectorModal');
                            var grid = document.getElementById('yearGrid');
                            if (!modal || !grid) {
                                console.error('Elementos do modal de ano não encontrados');
                                return;
                            }
                            grid.innerHTML = '';
                            var anoAtual = calendar.getDate().getFullYear();
                            var anos = [];
                            for (var i = -4; i <= 4; i++) {
                                anos.push(anoAtual + i);
                            }
                            anos.forEach(function(ano) {
                                var btn = document.createElement('button');
                                btn.textContent = ano;
                                btn.className = 'bg-blue-500 text-white rounded px-4 py-2 m-1 hover:bg-blue-600 font-semibold w-full';
                                if (ano === anoAtual) {
                                    btn.className += ' ring-2 ring-blue-700';
                                }
                                btn.onclick = function() {
                                    calendar.gotoDate(new Date(ano, 0, 1));
                                    modal.style.display = 'none';
                                };
                                grid.appendChild(btn);
                            });
                            modal.style.display = 'flex';
                        }
                    }
                },
                buttonText: {
                    dayGridMonth: 'Mês',
                    timeGridDay: 'Dia',
                },
                events: function(info, successCallback) {
                    // Log para debug
                    // Debug removido para produção
                    
                    // Buscar agendamentos e adicionar eventos 'Fechado' e 'Background' para dias com agendamento
                    fetch('agendamentos.php?action=listar')
                        .then(function(response) {
                            if (!response.ok) {
                                throw new Error('Erro na resposta do servidor: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(function(eventos) {
                            // Debug removido para produção
                            
                            // Buscar configurações
                            return fetch('configuracoes.php?action=config')
                                .then(function(response) {
                                    if (!response.ok) {
                                        console.warn('Erro ao carregar configurações, usando padrão');
                                        return null;
                                    }
                                    return response.json();
                                })
                                .then(function(config) {
                                    var diasFechados = [];
                                    if (config && config.abertos && Array.isArray(config.abertos)) {
                                        for (var i = 0; i < 7; i++) {
                                            if (parseInt(config.abertos[i]) !== 1) diasFechados.push(i);
                                        }
                                    }
                                    
                                    // Gerar eventos 'Fechado' para o mês atual
                                    var fechados = [];
                                    var start = info.start;
                                    var end = info.end;
                                    var data = new Date(start);
                                    while (data < end) {
                                        var diaSemana = data.getDay();
                                        if (diasFechados.includes(diaSemana)) {
                                            fechados.push({
                                                title: 'Fechado',
                                                start: data.toISOString().slice(0,10),
                                                allDay: true,
                                                display: 'background',
                                                backgroundColor: '#f8d7da',
                                                borderColor: '#f8d7da',
                                                textColor: '#b71c1c',
                                                extendedProps: { fechado: true }
                                            });
                                        }
                                        data.setDate(data.getDate() + 1);
                                    }
                                    
                                    // Gerar eventos de fundo para dias com agendamento
                                    var diasComAgendamento = {};
                                    if (Array.isArray(eventos)) {
                                        eventos.forEach(function(ev) {
                                            var dia = ev.start.substring(0,10);
                                            if (!diasComAgendamento[dia] && !ev.extendedProps?.fechado) {
                                                diasComAgendamento[dia] = true;
                                            }
                                        });
                                    }
                                    var backgrounds = Object.keys(diasComAgendamento).map(function(dia) {
                                        return {
                                            start: dia,
                                            end: dia,
                                            display: 'background',
                                            backgroundColor: '#dbeafe', // azul claro
                                            allDay: true
                                        };
                                    });
                                    
                                    var todosEventos = eventos.concat(fechados).concat(backgrounds);
                                    // Debug removido para produção
                                    successCallback(todosEventos);
                                })
                                .catch(function(error) {
                                    console.error('Erro ao carregar configuração:', error);
                                    // Se der erro na configuração, ainda retorna os eventos
                                    successCallback(eventos || []);
                                });
                        })
                        .catch(function(error) {
                            console.error('Erro ao carregar agendamentos:', error);
                            successCallback([]);
                        });
                },
                dateClick: function(info) {
                    // Debug removido para produção
                    
                    // Verifica se o dia está aberto
                    fetch('configuracoes.php?action=config')
                        .then(function(response) {
                            if (!response.ok) {
                                throw new Error('Erro ao carregar configurações');
                            }
                            return response.json();
                        })
                        .then(function(config) {
                            var partes = info.dateStr.split('-');
                            var diaSemana = (new Date(partes[0], partes[1]-1, partes[2])).getDay();
                            var diasFechados = [];
                            if (config && config.abertos && Array.isArray(config.abertos)) {
                                for (var i = 0; i < 7; i++) {
                                    if (parseInt(config.abertos[i]) !== 1) diasFechados.push(i);
                                }
                            }
                            if (diasFechados.includes(diaSemana)) {
                                // Dia fechado - não abrir agenda
                                return;
                            }
                            // Redireciona para a agenda do dia
                            window.location.href = 'agendamentos.php?dia=' + info.dateStr;
                        })
                        .catch(function(error) {
                            console.error('Erro ao verificar configurações:', error);
                            // Se der erro, ainda assim redireciona
                            window.location.href = 'agendamentos.php?dia=' + info.dateStr;
                        });
                },
                eventClick: function(info) {
                    console.log('Clique no evento:', info.event);
                    
                    // Ao clicar em um evento, abrir modal para editar
                    var evento = info.event;
                    if (evento.extendedProps.fechado) return;
                    
                    openAgendaModal();
                    document.getElementById('agenda_id').value = evento.id;
                    document.getElementById('agenda_pet_id').value = evento.extendedProps.pet_id;
                    document.getElementById('agenda_cliente_id').value = evento.extendedProps.cliente_id;
                    document.getElementById('agenda_data').value = evento.startStr.substring(0,10);
                    document.getElementById('agenda_hora').value = evento.startStr.substring(11,16);
                    document.getElementById('agenda_servico').value = evento.extendedProps.servico;
                    document.getElementById('agenda_status').value = evento.extendedProps.status;
                    document.getElementById('agenda_observacoes').value = evento.extendedProps.observacoes;
                    document.getElementById('agendaModalTitle').innerText = 'Editar Agendamento';
                },
                loading: function(isLoading) {
                    console.log('Calendário carregando:', isLoading);
                },
                eventDidMount: function(info) {
                    console.log('Evento montado:', info.event.title);
                }
            });
            
            calendar.render();
            
            // Estilizar botões em azul
            setTimeout(function() {
                var btns = document.querySelectorAll('.fc-button');
                btns.forEach(function(btn) {
                    btn.classList.add('bg-blue-500','text-white','hover:bg-blue-600','font-semibold','rounded','border-0');
                });
            }, 300);

            // Filtro dinâmico de pets por cliente
            var clienteSelect = document.getElementById('agenda_cliente_id');
            var petSelect = document.getElementById('agenda_pet_id');
            if (clienteSelect && petSelect) {
                var allPetOptions = Array.from(petSelect.options);
                clienteSelect.addEventListener('change', function() {
                    var clienteId = this.value;
                    petSelect.innerHTML = '';
                    var opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'Selecione o pet';
                    petSelect.appendChild(opt);
                    allPetOptions.forEach(function(option) {
                        if (!option.value) return;
                        if (option.getAttribute('data-cliente') === clienteId) {
                            petSelect.appendChild(option.cloneNode(true));
                        }
                    });
                });
            }

            // Adicionar função abrirModalAgendamentosDia:
            window.abrirModalAgendamentosDia = function(dataStr) {
                var modal = document.getElementById('modalAgendamentosDia');
                var conteudo = document.getElementById('modalAgendamentosDiaConteudo');
                if (!modal || !conteudo) {
                    console.error('Elementos do modal não encontrados');
                    return;
                }
                document.getElementById('modalAgendamentosDiaTitulo').innerText = 'Agendamentos do dia ' + dataStr.split('-').reverse().join('/');
                conteudo.innerHTML = 'Carregando...';
                modal.style.display = 'flex';
                fetch('agendamentos-dia.php?data=' + dataStr + '&modal=1')
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Erro ao carregar dados do dia');
                        }
                        return response.text();
                    })
                    .then(function(html) { 
                        conteudo.innerHTML = html; 
                    })
                    .catch(function(error) {
                        console.error('Erro ao carregar agendamentos do dia:', error);
                        conteudo.innerHTML = '<p class="text-red-500">Erro ao carregar dados</p>';
                    });
            };

            // Funções globais para o modal de agendamento
            window.openAgendaModal = function() {
                var modal = document.getElementById('agendaModal');
                if (!modal) {
                    console.error('Modal de agendamento não encontrado');
                    return;
                }
                modal.style.display = 'flex';
                document.getElementById('agendaModalTitle').textContent = 'Novo Agendamento';
                document.getElementById('agendaForm').reset();
                document.getElementById('agenda_id').value = '';
            };
            
            window.closeAgendaModal = function() {
                var modal = document.getElementById('agendaModal');
                if (modal) {
                    modal.style.display = 'none';
                }
            };
            
            window.abrirNovoAgendamento = function(data, hora) {
                openAgendaModal();
                document.getElementById('agenda_data').value = data;
                document.getElementById('agenda_hora').value = hora;
            };

            // Adicionar listener para o formulário de agendamento
            var agendaForm = document.getElementById('agendaForm');
            if (agendaForm) {
                agendaForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    var formData = new FormData(this);
                    formData.append('action', 'salvar');
                    
                    fetch('agendamentos.php?action=salvar', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Erro na resposta do servidor');
                        }
                        return response.text();
                    })
                    .then(function(result) {
                        if (result === 'ok') {
                            alert('Agendamento salvo com sucesso!');
                            closeAgendaModal();
                            // Recarregar o calendário
                            calendar.refetchEvents();
                        } else {
                            alert('Erro ao salvar agendamento: ' + result);
                        }
                    })
                    .catch(function(error) {
                        console.error('Erro ao salvar agendamento:', error);
                        alert('Erro ao salvar agendamento. Verifique o console para mais detalhes.');
                    });
                });
            }

            document.querySelectorAll("li[data-debug='livre']").forEach(function(li) {
                console.log('Debug: horário livre encontrado:', li.innerText, li);
                li.addEventListener('click', function() {
                    console.log('Debug: clique disparado via JS em', this.innerText);
                });
            });
        });
        </script>
    </div>
    <?php
}

include 'layout.php'; 