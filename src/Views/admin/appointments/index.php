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
                
                <div style="display: flex; gap: 1rem;">
                    <a href="<?= BASE_URL ?>/admin/appointments/sendConfirmations" class="btn" style="background: #25D366; color: white;" onclick="return confirm('Isso enviará uma mensagem no WhatsApp para TODOS os pacientes com agendamento para AMANHÃ que ainda não confirmaram. Deseja continuar?');">
                        <i class="fa-brands fa-whatsapp"></i> Disparar Confirmações (Amanhã)
                    </a>
                    <a href="<?= BASE_URL ?>/admin/appointments/create" class="btn btn-primary">
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

<?php include __DIR__ . '/../../shared/footer.php'; ?>
