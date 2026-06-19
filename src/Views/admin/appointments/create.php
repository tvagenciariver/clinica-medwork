<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Agendamento - MedWork</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
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
            <?php if(isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type'] === 'error' ? 'danger' : $_SESSION['msg_type'] ?>">
                    <?= htmlspecialchars($_SESSION['msg']) ?>
                </div>
                <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
            <?php endif; ?>

            <div class="page-header">
                <h1 class="page-title"><i class="fa-solid fa-plus" style="color: var(--primary); margin-right: 0.5rem;"></i> Novo Agendamento</h1>
                <a href="<?= BASE_URL ?>/admin/appointments" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            </div>

            <div class="card" style="max-width: 800px;">
                <form action="<?= BASE_URL ?>/admin/appointments/store" method="POST">
                    
                    <div class="form-group">
                        <label class="form-label">Paciente *</label>
                        <select name="patient_id" class="form-control" required>
                            <option value="">-- Selecione o Paciente --</option>
                            <?php foreach($patients as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['main_phone']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                            O paciente precisa estar cadastrado no sistema (menu Pacientes).
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Especialidade / Setor *</label>
                        <select name="specialty_id" class="form-control" required>
                            <option value="">-- Selecione a Especialidade --</option>
                            <?php foreach($specialties as $spec): ?>
                                <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Data *</label>
                            <input type="date" name="appointment_date" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Hora *</label>
                            <input type="time" name="appointment_time" class="form-control" required>
                        </div>
                    </div>

                    <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Agendar Procedimento</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>
