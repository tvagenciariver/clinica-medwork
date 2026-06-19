<?php include __DIR__ . '/../../shared/header.php'; ?>

<div class="app-layout">
    <?php include __DIR__ . '/../../shared/sidebar.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . '/../../shared/topbar.php'; ?>

        <div class="content-area">
            <?php if(isset($msg)): ?>
                <div class="alert alert-<?= $msg_type === 'error' ? 'danger' : $msg_type ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 class="page-title"><i class="fa-solid fa-calendar-check" style="color: var(--primary); margin-right: 0.5rem;"></i> Agenda de Procedimentos</h1>
                
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

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data e Hora</th>
                            <th>Paciente</th>
                            <th>Procedimento</th>
                            <th>WhatsApp</th>
                            <th>Status</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($appointments)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Nenhum agendamento encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($appointments as $appt): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($appt['appointment_date'])) ?></strong><br>
                                        <small style="color: var(--text-muted);"><i class="fa-regular fa-clock"></i> <?= date('H:i', strtotime($appt['appointment_time'])) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($appt['patient_name']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($appt['procedure_name']) ?></td>
                                    <td>
                                        <?php if($appt['has_whatsapp']): ?>
                                            <span class="badge badge-success" title="<?= htmlspecialchars($appt['main_phone']) ?>"><i class="fa-brands fa-whatsapp"></i> Sim</span>
                                        <?php else: ?>
                                            <span class="badge badge-default">Não</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($appt['status'] === 'agendado'): ?>
                                            <span class="badge badge-warning">Agendado</span>
                                        <?php elseif($appt['status'] === 'confirmado'): ?>
                                            <span class="badge badge-success">Confirmado</span>
                                        <?php elseif($appt['status'] === 'cancelado'): ?>
                                            <span class="badge badge-danger">Cancelado</span>
                                        <?php elseif($appt['status'] === 'atendido'): ?>
                                            <span class="badge badge-info">Atendido</span>
                                        <?php elseif($appt['status'] === 'faltou'): ?>
                                            <span class="badge badge-default" style="background: #94a3b8; color: white;">Faltou</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= BASE_URL ?>/admin/appointments/edit/<?= $appt['id'] ?>" class="btn btn-sm btn-secondary" title="Editar / Mudar Status">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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

<?php include __DIR__ . '/../../shared/footer.php'; ?>
