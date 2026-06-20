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
        /* Forçar blocos de no máximo 5 em telas grandes */
        @media (min-width: 1400px) {
            .kanban-board {
                grid-template-columns: repeat(5, 1fr);
            }
        }
        .kanban-col {
            background: #f8fafc;
            border-radius: 8px;
            width: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
            min-height: 400px;
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
            min-height: 200px;
        }
        .kanban-body.drag-over {
            background: #e2e8f0;
            border-radius: 0 0 8px 8px;
        }
        .kanban-card {
            background: white;
            border-radius: 6px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: grab;
        }
        .kanban-card:active {
            cursor: grabbing;
        }
        .kanban-card.dragging {
            opacity: 0.5;
            transform: scale(0.95);
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
        .spec-badge {
            display: inline-block;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            color: white;
            margin-bottom: 0.5rem;
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

            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <h1 class="page-title" style="margin: 0;"><i class="fa-solid fa-calendar-check" style="color: var(--primary); margin-right: 0.5rem;"></i> Agenda Kanban</h1>
                    
                    <form method="GET" action="<?= BASE_URL ?>/admin/appointments" style="display: flex; align-items: center; gap: 0.5rem; background: white; padding: 0.25rem 0.5rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <label for="date-filter" style="font-size: 0.875rem; font-weight: 500; color: #64748b; margin: 0;">Filtrar Data:</label>
                        <input type="date" id="date-filter" name="date" value="<?= htmlspecialchars($filterDate ?? date('Y-m-d')) ?>" onchange="this.form.submit()" style="border: none; outline: none; font-family: inherit; color: #1e293b; background: transparent; cursor: pointer;">
                    </form>
                </div>
                
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
            // Organizar agendamentos por Status (agendado, confirmado, atendido, faltou, cancelado)
            $kanban = [
                'agendado' => ['name' => 'Pendente', 'color' => '#3b82f6', 'bgCard' => 'white', 'appointments' => []],
                'confirmado' => ['name' => 'Confirmado', 'color' => '#10b981', 'bgCard' => '#f0fdf4', 'appointments' => []],
                'atendido' => ['name' => 'Atendido', 'color' => '#64748b', 'bgCard' => '#f1f5f9', 'appointments' => []],
                'faltou' => ['name' => 'Faltou', 'color' => '#f59e0b', 'bgCard' => '#fef3c7', 'appointments' => []],
                'cancelado' => ['name' => 'Cancelado', 'color' => '#ef4444', 'bgCard' => '#fee2e2', 'appointments' => []]
            ];

            foreach ($appointments as $appt) {
                $status = $appt['status'];
                if (isset($kanban[$status])) {
                    $kanban[$status]['appointments'][] = $appt;
                } else {
                    $kanban['agendado']['appointments'][] = $appt; // fallback
                }
            }
            ?>

            <div class="kanban-board">
                <?php foreach($kanban as $statusKey => $col): ?>
                    <div class="kanban-col" data-status="<?= $statusKey ?>">
                        <div class="kanban-header" style="border-bottom-color: <?= htmlspecialchars($col['color']) ?>;">
                            <span style="color: <?= htmlspecialchars($col['color']) ?>;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; margin-bottom: 2px; margin-right: 4px;"></i> <?= htmlspecialchars($col['name']) ?></span>
                            <span class="badge badge-secondary count-badge"><?= count($col['appointments']) ?></span>
                        </div>
                        
                        <div class="kanban-body dropzone" data-status="<?= $statusKey ?>">
                            <?php foreach($col['appointments'] as $appt): ?>
                                <div class="kanban-card draggable-card" draggable="true" data-id="<?= $appt['id'] ?>" style="border-left-color: <?= htmlspecialchars($col['color']) ?>; background: <?= $col['bgCard'] ?>;">
                                    
                                    <div class="spec-badge" style="background: <?= htmlspecialchars($appt['specialty_color'] ?: '#64748b') ?>;">
                                        <?= htmlspecialchars($appt['specialty_name'] ?: 'Geral') ?>
                                    </div>

                                    <div class="k-card-title">
                                        <?= htmlspecialchars($appt['patient_name']) ?>
                                        <?php if($appt['has_whatsapp']): ?>
                                            <i class="fa-brands fa-whatsapp" style="color: #25D366; font-size: 0.875rem;" title="Possui WhatsApp"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="k-card-time">
                                        <i class="fa-regular fa-clock"></i> <?= date('H:i', strtotime($appt['appointment_time'])) ?>
                                    </div>
                                    
                                    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.5rem;">
                                        <strong>Proc:</strong> <?= htmlspecialchars($appt['procedure_name'] ?: 'Não especificado') ?>
                                    </div>

                                    <div class="k-card-actions">
                                        <a href="<?= BASE_URL ?>/admin/appointments/edit/<?= $appt['id'] ?>" class="btn btn-sm btn-secondary" style="padding: 4px 8px; font-size: 0.75rem;">
                                            <i class="fa-solid fa-pen"></i> Editar
                                        </a>
                                        <?php if($statusKey !== 'cancelado'): ?>
                                        <a href="<?= BASE_URL ?>/admin/appointments/cancel/<?= $appt['id'] ?>" class="btn btn-sm" style="background: #ef4444; color: white; padding: 4px 8px; font-size: 0.75rem;" onclick="return confirm('Deseja cancelar esta consulta e avisar via WhatsApp?');">
                                            <i class="fa-solid fa-xmark"></i> Cancela
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
        </div>
    </main>
</div>

<script>
// --- DRAG AND DROP KANBAN ---
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.draggable-card');
    const dropzones = document.querySelectorAll('.dropzone');

    cards.forEach(card => {
        card.addEventListener('dragstart', () => {
            card.classList.add('dragging');
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
        });
    });

    dropzones.forEach(zone => {
        zone.addEventListener('dragover', e => {
            e.preventDefault(); // Necessário para permitir o drop
            zone.classList.add('drag-over');
            const draggingCard = document.querySelector('.dragging');
            zone.appendChild(draggingCard);
        });

        zone.addEventListener('dragleave', e => {
            zone.classList.remove('drag-over');
        });

        zone.addEventListener('drop', async e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const draggingCard = document.querySelector('.dragging');
            
            if (draggingCard) {
                const newStatus = zone.getAttribute('data-status');
                const cardId = draggingCard.getAttribute('data-id');
                const colData = getKanbanColConfig(newStatus);
                
                // Atualiza cor e fundo na hora
                draggingCard.style.borderLeftColor = colData.color;
                draggingCard.style.background = colData.bgCard;
                
                if (newStatus === 'cancelado') {
                    // Se moveu para cancelado, remove o botão de cancelar
                    const cancelBtn = draggingCard.querySelector('.k-card-actions .btn[style*="background: #ef4444"]');
                    if(cancelBtn) cancelBtn.remove();
                }

                // Atualiza contadores
                updateCounters();

                // Fazer a requisição silenciosa pro servidor
                try {
                    let res = await fetch('<?= BASE_URL ?>/admin/appointments/updateStatusAjax/' + cardId, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: newStatus })
                    });
                    let data = await res.json();
                    if(data.status !== 'success') {
                        alert("Erro ao salvar o status: " + data.message);
                    }
                } catch(error) {
                    console.error("Fetch error: ", error);
                }
            }
        });
    });

    function getKanbanColConfig(status) {
        const configs = {
            'agendado': { color: '#3b82f6', bgCard: 'white' },
            'confirmado': { color: '#10b981', bgCard: '#f0fdf4' },
            'atendido': { color: '#64748b', bgCard: '#f1f5f9' },
            'faltou': { color: '#f59e0b', bgCard: '#fef3c7' },
            'cancelado': { color: '#ef4444', bgCard: '#fee2e2' }
        };
        return configs[status] || configs['agendado'];
    }

    function updateCounters() {
        document.querySelectorAll('.kanban-col').forEach(col => {
            const count = col.querySelectorAll('.draggable-card').length;
            col.querySelector('.count-badge').textContent = count;
        });
    }
});

// --- DISPARO EM LOTE ---
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
                // Intervalo aleatório entre 4 e 9 segundos
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
