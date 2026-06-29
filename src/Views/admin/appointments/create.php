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
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <select name="patient_id" id="patient_id" class="form-control" required style="flex: 1;">
                                <option value="">-- Selecione o paciente --</option>
                                <?php foreach($patients as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?> (CPF: <?= htmlspecialchars($p['main_phone'] ?? 'Sem telefone') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('modalNovoPaciente').style.display = 'flex'" style="white-space: nowrap;">
                                <i class="fa-solid fa-plus"></i> Novo Paciente
                            </button>
                        </div>
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
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Data do Agendamento *</label>
                            <input type="date" name="appointment_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Horário Fixo (Opcional)</label>
                            <input type="time" name="appointment_time" class="form-control">
                            <small style="color: #64748b;">Deixe em branco para controle por Ordem de Chegada.</small>
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

<!-- Modal Novo Paciente -->
<div id="modalNovoPaciente" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem;">Cadastrar Novo Paciente</h2>
        
        <form id="formNovoPaciente" onsubmit="salvarNovoPaciente(event)">
            <div class="form-group">
                <label class="form-label">Nome Completo *</label>
                <input type="text" id="modal_full_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">CPF *</label>
                <input type="text" id="modal_cpf" class="form-control" required placeholder="Apenas números">
            </div>
            <div class="form-group">
                <label class="form-label">Telefone / Celular</label>
                <input type="text" id="modal_phone" class="form-control">
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" id="modal_has_whatsapp" value="1" checked>
                    <span class="form-label" style="margin: 0;">Este número possui WhatsApp</span>
                </label>
            </div>
            
            <div id="modal-error" style="color: #ef4444; font-size: 0.9rem; margin-bottom: 1rem; display: none;"></div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalNovoPaciente').style.display = 'none'">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnSalvarPaciente">Salvar Paciente</button>
            </div>
        </form>
    </div>
</div>

<script>
async function salvarNovoPaciente(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSalvarPaciente');
    const errorBox = document.getElementById('modal-error');
    
    const fullName = document.getElementById('modal_full_name').value;
    const cpf = document.getElementById('modal_cpf').value;
    const phone = document.getElementById('modal_phone').value;
    const hasWhatsapp = document.getElementById('modal_has_whatsapp').checked;

    btn.disabled = true;
    btn.innerText = 'Salvando...';
    errorBox.style.display = 'none';

    try {
        const response = await fetch('<?= BASE_URL ?>/admin/patients/storeAjax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                full_name: fullName,
                cpf: cpf,
                main_phone: phone,
                has_whatsapp: hasWhatsapp
            })
        });

        const data = await response.json();

        if (data.status === 'success') {
            // Sucesso! Adicionar no select e selecionar
            const select = document.getElementById('patient_id');
            const option = document.createElement('option');
            option.value = data.patient.id;
            option.text = `${data.patient.full_name} (CPF: ${data.patient.cpf})`;
            select.appendChild(option);
            select.value = data.patient.id;

            // Fechar modal
            document.getElementById('modalNovoPaciente').style.display = 'none';
            document.getElementById('formNovoPaciente').reset();
        } else {
            errorBox.innerText = data.message || 'Erro ao salvar paciente.';
            errorBox.style.display = 'block';
        }
    } catch (err) {
        errorBox.innerText = 'Erro de comunicação com o servidor.';
        errorBox.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.innerText = 'Salvar Paciente';
    }
}
</script>
</body>
</html>
