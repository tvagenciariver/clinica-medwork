<?php include __DIR__ . '/../../shared/header.php'; ?>

<div class="app-layout">
    <?php include __DIR__ . '/../../shared/sidebar.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . '/../../shared/topbar.php'; ?>

        <div class="content-area">
            <?php if(isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type'] === 'error' ? 'danger' : $_SESSION['msg_type'] ?>">
                    <?= htmlspecialchars($_SESSION['msg']) ?>
                </div>
                <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
            <?php endif; ?>

            <div class="page-header">
                <h1 class="page-title"><i class="fa-solid fa-pen" style="color: var(--primary); margin-right: 0.5rem;"></i> Editar Agendamento #<?= $appointment['id'] ?></h1>
                <a href="<?= BASE_URL ?>/admin/appointments" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            </div>

            <div class="card" style="max-width: 800px;">
                <form action="<?= BASE_URL ?>/admin/appointments/update/<?= $appointment['id'] ?>" method="POST">
                    
                    <div class="form-group">
                        <label class="form-label">Paciente *</label>
                        <select name="patient_id" class="form-control" required>
                            <?php foreach($patients as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $p['id'] == $appointment['patient_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['main_phone']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Procedimento ou Exame *</label>
                        <input type="text" name="procedure_name" class="form-control" value="<?= htmlspecialchars($appointment['procedure_name']) ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Data *</label>
                            <input type="date" name="appointment_date" class="form-control" value="<?= htmlspecialchars($appointment['appointment_date']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Hora *</label>
                            <input type="time" name="appointment_time" class="form-control" value="<?= htmlspecialchars($appointment['appointment_time']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <label class="form-label">Status da Consulta</label>
                        <select name="status" class="form-control">
                            <option value="agendado" <?= $appointment['status'] === 'agendado' ? 'selected' : '' ?>>Agendado (Pendente)</option>
                            <option value="confirmado" <?= $appointment['status'] === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                            <option value="cancelado" <?= $appointment['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            <option value="atendido" <?= $appointment['status'] === 'atendido' ? 'selected' : '' ?>>Atendido / Finalizado</option>
                            <option value="faltou" <?= $appointment['status'] === 'faltou' ? 'selected' : '' ?>>Faltou (No-Show)</option>
                        </select>
                        <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                            Se o paciente responder SIM ou NÃO via WhatsApp, esse status mudará automaticamente para Confirmado ou Cancelado.
                        </small>
                    </div>

                    <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../shared/footer.php'; ?>
