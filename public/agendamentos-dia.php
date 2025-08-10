<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/Agendamento.php';

// Carregar configurações
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
    $data = json_decode($json, true);
    if (is_array($data)) {
        $config = array_merge($config, $data);
    }
}

// Obter data da query string
$data = $_GET['data'] ?? date('Y-m-d');
$dia_semana = date('w', strtotime($data)); // 0=domingo, 6=sabado

// Verificar se o dia está aberto
$dia_aberto = isset($config['abertos'][$dia_semana]) ? $config['abertos'][$dia_semana] : 1;
$intervalo_dia = isset($config['intervalos'][$dia_semana]) ? $config['intervalos'][$dia_semana] : $config['intervalo'];

// Buscar agendamentos do dia
$agendamentos = array_filter(Agendamento::listarTodos(), function($a) use ($data) {
    return substr($a['data'], 0, 10) === $data;
});
$horariosOcupados = array_map(function($a) { return substr($a['hora'],0,5); }, $agendamentos);

// Gerar lista de horários apenas se o dia estiver aberto
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

// No início do PHP, adicionar exclusão se $_GET['excluir'] estiver presente:
if (isset($_GET['excluir'])) {
    Agendamento::deletar($_GET['excluir']);
    header('Location: agendamentos-dia.php?data=' . urlencode($data));
    exit;
}

include 'layout.php';

function render_content() {
    global $data, $lista, $horariosOcupados, $agendamentos, $dia_aberto;
?>
<div class="max-w-xl mx-auto mt-8 space-y-6">
    <h2 class="text-2xl font-bold mb-4">Agendamentos do dia <?= date('d/m/Y', strtotime($data)) ?></h2>
    <div class="bg-white rounded-lg shadow p-6">
        <?php if ($dia_aberto): ?>
            <ul class="divide-y">
                <?php foreach ($lista as $horario): ?>
                    <?php $ocupado = in_array($horario, $horariosOcupados); ?>
                    <li class="py-2 flex items-center justify-between <?= $ocupado ? '' : 'cursor-pointer hover:bg-blue-50' ?>" <?= $ocupado ? '' : "onclick=\"window.location.href='agendamentos.php?data=$data&hora=$horario&novo=1'\"" ?> >
                        <span class="<?= $ocupado ? 'text-gray-400' : 'text-gray-800' ?>"><?= $horario ?></span>
                        <?php if ($ocupado): ?>
                            <?php 
                            // Exibir info do agendamento
                            foreach ($agendamentos as $a) {
                                if (substr($a['hora'],0,5) === $horario) {
                                    $primeiro_nome = explode(' ', $a['cliente_nome'])[0];
                                    echo '<span class="text-xs text-red-500 ml-2">' . htmlspecialchars($primeiro_nome) . ' | ' . htmlspecialchars($a['pet_nome']) . ' | ' . htmlspecialchars($a['servico']) . '</span>';
                                    echo ' <a href="agendamentos-dia.php?data=' . urlencode($data) . '&excluir=' . $a['id'] . '" onclick="return confirm(\'Excluir este agendamento?\')" style="margin-left:8px;color:#d00;" title="Excluir"><i class="fas fa-trash"></i></a>';
                                }
                            }
                            ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<script>
// Debug: loga todos os cliques nos horários disponíveis
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('li.cursor-pointer').forEach(function(li) {
        li.addEventListener('click', function(e) {
            console.log('Clique detectado no horário:', this.innerText);
        });
    });
});
function abrirModalNovoAgendamento(data, hora) {
    // Função para abrir modal de agendamento na página principal, se desejar
    alert('Agendar para ' + data + ' às ' + hora);
    // Aqui você pode implementar redirecionamento ou abrir modal conforme sua lógica
}
</script>
<?php }
?> 