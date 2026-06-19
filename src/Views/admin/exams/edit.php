<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Exame - MedWork</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .company-field { display: <?= $exam['origin'] === 'company' ? 'block' : 'none' ?>; }
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
            <div class="page-header">
                <h1 class="page-title">Editar Exame</h1>
                <a href="<?= BASE_URL ?>/admin/exams" class="btn btn-secondary">Voltar</a>
            </div>

            <?php if(!empty($_SESSION['msg'])): ?>
                <div class="alert <?= ($_SESSION['msg_type'] === 'error') ? 'alert-danger' : 'alert-success' ?>" style="display: flex; align-items: center; gap: 0.75rem; border-left: 4px solid <?= ($_SESSION['msg_type'] === 'error') ? '#ef4444' : '#10b981' ?>; box-shadow: var(--shadow-sm); font-weight: 500;">
                    <i class="fa-solid <?= ($_SESSION['msg_type'] === 'error') ? 'fa-circle-exclamation' : 'fa-circle-check' ?>" style="font-size: 1.25rem;"></i>
                    <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 800px;">
                <form action="<?= BASE_URL ?>/admin/exams/update/<?= $exam['id'] ?>" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label class="form-label">Protocolo</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($exam['protocol_code']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Paciente *</label>
                        <select name="patient_id" class="form-control" required>
                            <option value="">-- Selecione o paciente --</option>
                            <?php foreach($patients as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $exam['patient_id'] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['full_name']) ?> (CPF: <?= htmlspecialchars($p['cpf']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Origem do Atendimento *</label>
                            <select name="origin" id="originSelect" class="form-control" required>
                                <option value="private" <?= $exam['origin'] === 'private' ? 'selected' : '' ?>>Particular</option>
                                <option value="company" <?= $exam['origin'] === 'company' ? 'selected' : '' ?>>Encaminhado por Empresa</option>
                            </select>
                        </div>
                        <div class="form-group company-field" id="companyWrapper" style="flex: 1;">
                            <label class="form-label">Empresa Vinculada *</label>
                            <select name="company_id" id="companySelect" class="form-control" <?= $exam['origin'] === 'company' ? 'required' : '' ?>>
                                <option value="">-- Selecione a empresa --</option>
                                <?php foreach($companies as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $exam['company_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['trade_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 2;">
                            <label class="form-label">Tipo de Exame (Ex: ASO, Audiometria) *</label>
                            <input type="text" name="exam_type" class="form-control" required value="<?= htmlspecialchars($exam['exam_type']) ?>">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Data do Exame *</label>
                            <input type="text" name="exam_date" class="form-control" required value="<?= date('d/m/Y', strtotime($exam['exam_date'])) ?>" placeholder="DD/MM/AAAA"
                                   oninput="this.value = this.value.replace(/\D/g, '').replace(/^(\d{2})(\d)/, '$1/$2').replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3').substring(0,10);">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Médico Responsável</label>
                        <input type="text" name="responsible_doctor" class="form-control" value="<?= htmlspecialchars($exam['responsible_doctor'] ?? '') ?>">
                    </div>

                    <?php
                        $current_paths = [];
                        if (!empty($exam['file_path'])) {
                            $decoded = json_decode($exam['file_path'], true);
                            $current_paths = is_array($decoded) ? $decoded : [$exam['file_path']];
                        }
                    ?>

                    <?php if(!empty($current_paths)): ?>
                    <div class="form-group" style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <label class="form-label" style="margin-bottom: 0.5rem;">Arquivos Atuais (<?= count($current_paths) ?>)</label>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <?php foreach($current_paths as $index => $path): ?>
                                <div style="display: flex; align-items: center; justify-content: space-between; background: white; padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($path) ?>" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 500;">
                                        <i class="fa-solid fa-file"></i> Arquivo <?= $index + 1 ?>
                                    </a>
                                    <label style="color: #ef4444; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 0.3rem;">
                                        <input type="checkbox" name="delete_files[]" value="<?= $index ?>"> 
                                        <i class="fa-solid fa-trash"></i> Excluir
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Adicionar Novos Arquivos</label>
                        <input type="file" name="exam_files[]" class="form-control" accept=".pdf,image/*" multiple>
                        <small style="color: #64748b; margin-top: 0.5rem; display: block;">
                            Você pode selecionar múltiplos arquivos (PDF ou Imagens). Eles serão <strong>adicionados</strong> aos arquivos que já existem neste exame.
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Atual</label>
                        <select name="status" class="form-control" required>
                            <option value="registered" <?= $exam['status'] === 'registered' ? 'selected' : '' ?>>Cadastrado</option>
                            <option value="available" <?= $exam['status'] === 'available' ? 'selected' : '' ?>>Disponível</option>
                            <option value="sent_whatsapp" <?= $exam['status'] === 'sent_whatsapp' ? 'selected' : '' ?>>Notificado no WhatsApp</option>
                            <option value="viewed_company" <?= $exam['status'] === 'viewed_company' ? 'selected' : '' ?>>Visualizado pela Empresa</option>
                            <option value="viewed_patient" <?= $exam['status'] === 'viewed_patient' ? 'selected' : '' ?>>Visualizado pelo Paciente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observações Clínicas / Internas</label>
                        <textarea name="observations" class="form-control" rows="3"><?= htmlspecialchars($exam['observations'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="allow_whatsapp" value="1" <?= $exam['allow_whatsapp'] ? 'checked' : '' ?>>
                            <span class="form-label" style="margin: 0;">Permitir notificação por WhatsApp quando disponível?</span>
                        </label>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('originSelect').addEventListener('change', function() {
    var companyWrapper = document.getElementById('companyWrapper');
    var companySelect = document.getElementById('companySelect');
    if (this.value === 'company') {
        companyWrapper.style.display = 'block';
        companySelect.setAttribute('required', 'required');
    } else {
        companyWrapper.style.display = 'none';
        companySelect.removeAttribute('required');
    }
});
</script>

</body>
</html>
