<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda Kanban - MedWork</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            padding-bottom: 2rem;
            align-items: flex-start;
        }
        /* Forçar blocos de no máximo 4 em telas grandes */
        @media (min-width: 1400px) {
            .kanban-board {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .kanban-col {
            background: #f8fafc;
            border-radius: 8px;
            width: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
            max-height: calc(100vh - 200px);
        }
        .kanban-header {
            padding: 1rem;
            border-bottom: 2px solid;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border-radius: 8px 8px 0 0;
        }
        .kanban-body {
            padding: 1rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .kanban-card {
            background: white;
            border-radius: 6px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
            position: relative;
        }
        .k-card-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        .k-card-time {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .k-card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 0.5rem;
            border-top: 1px solid #f1f5f9;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="app-layout">
    <?php include __DIR__ . '/../../shared/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div></div>
            <div class="user-profile flex items-center gap-2">
                <span style="font-weight: 500;"><?= htmlspecialchars($_SESSION['name']) ?></span>
            </div>
        </header>

        <div class="content-area">
            <?php if(isset($msg)): ?>
                <div class="alert <?= ($msg_type === 'error') ? 'alert-danger' : 'alert-success' ?>" style="display: flex; align-items: center; gap: 0.75rem; border-left: 4px solid <?= ($msg_type === 'error') ? '#ef4444' : '#10b981' ?>; box-shadow: var(--shadow-sm); font-weight: 500;">
                    <i class="fa-solid <?= ($msg_type === 'error') ? 'fa-circle-exclamation' : 'fa-circle-check' ?>" style="font-size: 1.25rem;"></i>
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 class="page-title"><i class="fa-solid fa-calendar-check" style="color: var(--primary); margin-right: 0.5rem;"></i> Agenda Kanban</h1>
                
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button type="button" id="btn-disparar" class="btn" style="background: #25D366; color: white;" onclick="startWahaQueue()">
                        <i class="fa-brands fa-whatsapp"></i> Disparar Confirmações (Amanhã)
                    </button>
                    <span id="queue-status" style="display: none; font-weight: bold; color: var(--text-muted);">
                        <i class="fa-solid fa-spinner fa-spin"></i> <span id="queue-text">Iniciando...</span>
                    </span>
                    <a href="<?= BASE_URL ?>/admin/appointments/create" class="btn btn-primary" id="btn-novo">
                        <i class="fa-solid fa-plus"></i> Novo Agendamento
                    </a>
                </div>
            </div>

            <?php
            // Organizar agendamentos por especialidade
            $kanban = [];
            foreach ($specialties as $spec) {
                $kanban[$spec['id']] = [
                    'name' => $spec['name'],
                    'color' => $spec['color_hex'],
                    'appointments' => []
                ];
            }
            $kanban['sem_setor'] = [
                'name' => 'Geral / Sem Setor',
                'color' => '#64748b',
                'appointments' => []
            ];

            foreach ($appointments as $appt) {
                if ($appt['specialty_id'] && isset($kanban[$appt['specialty_id']])) {
                    $kanban[$appt['specialty_id']]['appointments'][] = $appt;
                } else {
                    $kanban['sem_setor']['appointments'][] = $appt;
                }
            }
            ?>

            <div class="kanban-board">
                <?php foreach($kanban as $colId => $col): ?>
                    <?php if(empty($col['appointments']) && $colId === 'sem_setor') continue; // Oculta 'Sem setor' se estiver vazio ?>
                    
                    <div class="kanban-col">
                        <div class="kanban-header" style="border-bottom-color: <?= htmlspecialchars($col['color']) ?>;">
                            <span><?= htmlspecialchars($col['name']) ?></span>
                            <span class="badge badge-secondary"><?= count($col['appointments']) ?></span>
                        </div>
                        
                        <div class="kanban-body">
                            <?php if (empty($col['appointments'])): ?>
                                <div style="text-align: center; color: #94a3b8; font-size: 0.875rem; padding: 2rem 0;">
                                    Nenhum agendamento.
                                </div>
                            <?php else: ?>
                                <?php foreach($col['appointments'] as $appt): ?>
                                    <div class="kanban-card" style="border-left-color: <?= htmlspecialchars($col['color']) ?>;">
                                        <div class="k-card-title">
                                            <?= htmlspecialchars($appt['patient_name']) ?>
                                            <?php if($appt['has_whatsapp']): ?>
                                                <i class="fa-brands fa-whatsapp" style="color: #25D366; font-size: 0.875rem;" title="Possui WhatsApp"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="k-card-time">
                                            <i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($appt['appointment_date'])) ?>
                                            <i class="fa-regular fa-clock" style="margin-left: 0.5rem;"></i> <?= date('H:i', strtotime($appt['appointment_time'])) ?>
                                        </div>
                                        
                                        <div style="margin-bottom: 0.5rem;">
                                            <?php if($appt['status'] === 'agendado'): ?>
                                                <span class="status-badge" style="background: #e0e7ff; color: #4338ca;">Pendente</span>
                                            <?php elseif($appt['status'] === 'confirmado'): ?>
                                                <span class="status-badge" style="background: #dcfce7; color: #15803d;">Confirmado</span>
                                            <?php elseif($appt['status'] === 'atendido'): ?>
                                                <span class="status-badge" style="background: #f1f5f9; color: #475569;">Atendido</span>
                                            <?php elseif($appt['status'] === 'cancelado'): ?>
                                                <span class="status-badge" style="background: #fee2e2; color: #b91c1c;">Cancelado</span>
                                            <?php elseif($appt['status'] === 'faltou'): ?>
                                                <span class="status-badge" style="background: #fef3c7; color: #b45309;">Faltou</span>
                                            <?php endif; ?>
                                        </div>

                                        <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.5rem;">
                                            <strong>Proc:</strong> <?= htmlspecialchars($appt['procedure_name'] ?: 'Não especificado') ?>
                                        </div>

                                        <div class="k-card-actions">
                                            <a href="<?= BASE_URL ?>/admin/appointments/edit/<?= $appt['id'] ?>" class="btn btn-sm btn-secondary" style="padding: 4px 8px; font-size: 0.75rem;">
                                                <i class="fa-solid fa-pen"></i> Editar
                                            </a>
                                            <?php if($appt['status'] !== 'cancelado'): ?>
                                            <a href="<?= BASE_URL ?>/admin/appointments/cancel/<?= $appt['id'] ?>" class="btn btn-sm" style="background: #ef4444; color: white; padding: 4px 8px; font-size: 0.75rem;" onclick="return confirm('Deseja cancelar esta consulta e avisar via WhatsApp?');">
                                                <i class="fa-solid fa-xmark"></i> Cancelar
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
        </div>
    </main>
</div>

<script>
async function startWahaQueue() {
    if(!confirm('Isso enviará uma mensagem no WhatsApp para TODOS os pacientes com agendamento para AMANHÃ que ainda não confirmaram. Será feito de forma cadenciada para evitar bloqueios. Deseja continuar?')) {
        return;
    }

    const btn = document.getElementById('btn-disparar');
    const statusBox = document.getElementById('queue-status');
    const statusText = document.getElementById('queue-text');
    const btnNovo = document.getElementById('btn-novo');

    btn.style.display = 'none';
    btnNovo.style.display = 'none';
    statusBox.style.display = 'inline-block';

    try {
        statusText.innerText = "Buscando pacientes agendados...";
        let res = await fetch('<?= BASE_URL ?>/admin/appointments/getTomorrowIds');
        let data = await res.json();
        
        let ids = data.ids || [];
        if(ids.length === 0) {
            alert('Nenhum agendamento pendente para amanhã com número de WhatsApp cadastrado.');
            location.reload();
            return;
        }

        let sucessos = 0;
        let erros = 0;

        for (let i = 0; i < ids.length; i++) {
            statusText.innerText = `Enviando mensagem ${i+1} de ${ids.length}...`;
            
            let sendRes = await fetch('<?= BASE_URL ?>/admin/appointments/sendSingle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: ids[i] })
            });
            let sendData = await sendRes.json();
            
            if(sendData.status === 'success') sucessos++;
            else erros++;

            if(i < ids.length - 1) {
                // Intervalo aleatório entre 4 e 9 segundos para parecer humano
                let delay = Math.floor(Math.random() * 5000) + 4000;
                statusText.innerText = `Aguardando ${Math.round(delay/1000)}s (proteção anti-spam)...`;
                await new Promise(r => setTimeout(r, delay));
            }
        }

        alert(`Concluído! ${sucessos} mensagens enviadas. ${erros > 0 ? erros + ' falhas.' : ''}`);
        location.reload();
        
    } catch(e) {
        alert('Ocorreu um erro no disparo em lote. Tente novamente.');
        location.reload();
    }
}
</script>

</body>
</html>
