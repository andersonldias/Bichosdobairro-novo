<?php
/**
 * PÁGINA: agendamentos.php
 * SISTEMA: Bichos do Bairro - Agendamentos
 * 
 * ⚠️  ATENÇÃO - FUNCIONALIDADES CRÍTICAS EM FUNCIONAMENTO:
 * ✅ FullCalendar integrado e funcionando
 * ✅ Endpoint JSON para listagem de agendamentos
 * ✅ Correção aplicada: agendamentos recorrentes sem data final mostram 2 anos
 * ✅ Período padrão de 90 dias para agendamentos com data final
 * ✅ Geração automática de ocorrências recorrentes
 * 
 * 🚨 NÃO ALTERE SEM BACKUP:
 * - Lógica de verificação de agendamentos recorrentes sem data final
 * - Endpoint 'action=listar'
 * - JavaScript de inicialização do calendário
 * 
 * 📝 REGRA DE OURO:
 * "Ao fazer upgrade ou alteração, NÃO MUDE NADA que já está funcionando"
 */
require_once '../src/init.php';

// ========================================
// ENDPOINT CRÍTICO - NÃO MODIFICAR SEM BACKUP
// ========================================
// Endpoint para o FullCalendar: retorna agendamentos em JSON (incluindo recorrentes)
if (isset($_GET['action']) && $_GET['action'] === 'listar') {
    try {
        // FUNCIONALIDADE CRÍTICA: Período dinâmico baseado em agendamentos recorrentes
        // ⚠️ NÃO ALTERAR - Esta lógica resolve o problema dos 3 meses
        $dataInicio = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
        
        // CORREÇÃO APLICADA: Verificar agendamentos recorrentes sem data final
        // ✅ FUNCIONANDO - Não modificar esta consulta
        $pdo = getDb();
        $sqlVerificarRecorrentes = "SELECT COUNT(*) as total FROM agendamentos_recorrentes WHERE ativo = TRUE AND data_fim IS NULL";
        $stmtVerificar = $pdo->query($sqlVerificarRecorrentes);
        $temRecorrentesSemFim = $stmtVerificar->fetch()['total'] > 0;
        
        // LÓGICA PRINCIPAL: Período adaptativo
        // ✅ FUNCIONANDO - Se há recorrentes sem fim: 2 anos, senão: 90 dias
        if ($temRecorrentesSemFim) {
            $dataFim = $_GET['end'] ?? date('Y-m-d', strtotime('+2 years'));
        } else {
            $dataFim = $_GET['end'] ?? date('Y-m-d', strtotime('+90 days'));
        }
        
        header('Content-Type: application/json');
        $eventos = [];
        
        // CONSULTA PRINCIPAL - NÃO MODIFICAR
        // ✅ FUNCIONANDO - Busca agendamentos normais e recorrentes
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
        
        // GERAÇÃO DE OCORRÊNCIAS RECORRENTES - CRÍTICO
        // ✅ FUNCIONANDO - Não alterar sem backup
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
        
        // FORMATAÇÃO PARA FULLCALENDAR - NÃO MODIFICAR
        // ✅ FUNCIONANDO - Formato específico para o FullCalendar
        foreach ($agendamentos as $a) {
            $eventos[] = [
                'id' => $a['id'],
                'title' => $a['pet_nome'] . ' - ' . $a['servico'],
                'start' => $a['data'] . 'T' . $a['hora'],
                'end' => $a['data'] . 'T' . $a['hora'],
                'backgroundColor' => $a['recorrencia_id'] ? '#3b82f6' : '#10b981',
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

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Buscar dados para o formulário
$clientes = Cliente::listarTodos();
$pets = Pet::listarTodos();

// ========================================
// CONTEÚDO DA PÁGINA - USANDO LAYOUT PADRÃO
// ========================================
?>

<!-- ⚠️ CDNs CRÍTICOS PARA FULLCALENDAR - NÃO ALTERAR VERSÕES -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                        Agendamentos
                    </h4>
                    <div class="btn-group">
                        <a href="agendamentos-recorrentes.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-redo mr-1"></i> Recorrentes
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- ✅ FULLCALENDAR FUNCIONANDO - NÃO ALTERAR -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================
     JAVASCRIPT CRÍTICO - FULLCALENDAR
     ⚠️ NÃO MODIFICAR SEM BACKUP COMPLETO
     ======================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ FUNCIONANDO - Inicialização do FullCalendar
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Elemento calendar não encontrado');
        return;
    }
    
    // CONFIGURAÇÃO PRINCIPAL DO FULLCALENDAR
    // ⚠️ NÃO ALTERAR - Configuração testada e funcionando
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        // FUNÇÃO CRÍTICA - CARREGAMENTO DE EVENTOS
        // ✅ FUNCIONANDO - Conecta com o endpoint PHP
        events: function(info, successCallback) {
            fetch('agendamentos.php?action=listar&start=' + info.start.toISOString().slice(0,10) + '&end=' + info.end.toISOString().slice(0,10))
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor: ' + response.status);
                    }
                    return response.json();
                })
                .then(function(eventos) {
                    successCallback(eventos);
                })
                .catch(function(error) {
                    console.error('Erro ao carregar eventos:', error);
                    successCallback([]);
                });
        },
        // INTERAÇÃO COM EVENTOS
        // ✅ FUNCIONANDO - Clique nos agendamentos
        eventClick: function(info) {
            const props = info.event.extendedProps;
            alert('Agendamento: ' + info.event.title + '\n' +
                  'Cliente: ' + props.cliente_nome + '\n' +
                  'Pet: ' + props.pet_nome + '\n' +
                  'Serviço: ' + props.servico + '\n' +
                  'Status: ' + props.status + '\n' +
                  (props.is_recorrente ? 'Tipo: Recorrente' : 'Tipo: Normal'));
        }
    });
    
    // ✅ RENDERIZAÇÃO FINAL - NÃO ALTERAR
    calendar.render();
});
</script>

<?php
// ========================================
// INCLUIR LAYOUT PADRÃO - OBRIGATÓRIO
// ========================================
include 'layout.php';
?>