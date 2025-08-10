<?php
// Página de Configurações do Sistema - Horário de Funcionamento da Agenda
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$msg = '';
$erro = '';

// Carregar configurações atuais (se existirem)
$config_file = __DIR__ . '/../config_agenda.json';
$config = [
    'inicio' => '08:00',
    'fim' => '18:00',
    'intervalo' => 20
];
if (file_exists($config_file)) {
    $json = file_get_contents($config_file);
    $data = json_decode($json, true);
    if (is_array($data)) {
        $config = array_merge($config, $data);
    }
}

// Dias da semana
$nomes_dias = ['domingo','segunda','terca','quarta','quinta','sexta','sabado'];
$labels_dias = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
// Valores padrão para dias abertos e intervalos
$abertos = $config['abertos'] ?? [1,1,1,1,1,1,1];
$intervalos = $config['intervalos'] ?? array_fill(0,7,$config['intervalo']);

// Salvar configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inicio = $_POST['inicio'] ?? '08:00';
    $fim = $_POST['fim'] ?? '18:00';
    $intervalo = intval($_POST['intervalo'] ?? 20);
    $abertos = array_map('intval', $_POST['abertos'] ?? []);
    $intervalos = array_map('intval', $_POST['intervalos'] ?? []);
    // Garante que todos os dias estejam presentes
    $abertos_full = [];
    $intervalos_full = [];
    for ($i=0; $i<7; $i++) {
        $abertos_full[$i] = in_array($i, $abertos) ? 1 : 0;
        $intervalos_full[$i] = $intervalos[$i] ?? $intervalo;
    }
    $config = [
        'inicio' => $inicio,
        'fim' => $fim,
        'intervalo' => $intervalo,
        'abertos' => $abertos_full,
        'intervalos' => $intervalos_full
    ];
    if (file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT))) {
        $msg = 'Configurações salvas com sucesso!';
    } else {
        $erro = 'Erro ao salvar configurações!';
    }
}

// Endpoint para retornar configurações em JSON
if (isset($_GET['action']) && $_GET['action'] === 'config') {
    header('Content-Type: application/json');
    echo json_encode($config);
    exit;
}

include 'layout.php';

function render_content() {
    global $msg, $erro, $config, $labels_dias, $abertos, $intervalos;
?>
<div class="max-w-xl mx-auto mt-8 space-y-6">
    <h2 class="text-2xl font-bold mb-4">Preferências do Sistema</h2>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-2">Horário de Funcionamento da Agenda</h3>
        <form method="post" class="space-y-4">
            <div class="flex gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Início</label>
                    <input type="time" name="inicio" value="<?= htmlspecialchars($config['inicio']) ?>" required class="px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fim</label>
                    <input type="time" name="fim" value="<?= htmlspecialchars($config['fim']) ?>" required class="px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Intervalo padrão (min)</label>
                    <input type="number" name="intervalo" min="1" value="<?= htmlspecialchars($config['intervalo']) ?>" required class="px-3 py-2 border border-gray-300 rounded-md w-24">
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Salvar</button>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Dias de funcionamento:</label>
                <div class="flex flex-wrap gap-4">
                    <?php foreach ($labels_dias as $i => $label): ?>
                        <label class="flex items-center gap-1">
                            <input type="checkbox" name="abertos[]" value="<?= $i ?>" <?= ($abertos[$i] ?? 1) ? 'checked' : '' ?>>
                            <?= $label ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Intervalo entre agendamentos por dia da semana (min):</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($labels_dias as $i => $label): ?>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1"><?= $label ?></label>
                            <input type="number" name="intervalos[<?= $i ?>]" min="1" value="<?= htmlspecialchars($intervalos[$i] ?? $config['intervalo']) ?>" class="px-2 py-1 border border-gray-300 rounded-md w-20">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
        <?php if ($msg): ?>
            <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded-lg">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-lg">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php } 