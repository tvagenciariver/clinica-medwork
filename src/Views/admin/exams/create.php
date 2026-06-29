<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Exame - MedWork</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .company-field { display: none; }
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
                <h1 class="page-title">Lançar Novo Exame</h1>
                <a href="<?= BASE_URL ?>/admin/exams" class="btn btn-secondary">Voltar</a>
            </div>

            <?php if(!empty($_SESSION['msg'])): ?>
                <div class="alert <?= ($_SESSION['msg_type'] === 'error') ? 'alert-danger' : 'alert-success' ?>" style="display: flex; align-items: center; gap: 0.75rem; border-left: 4px solid <?= ($_SESSION['msg_type'] === 'error') ? '#ef4444' : '#10b981' ?>; box-shadow: var(--shadow-sm); font-weight: 500;">
                    <i class="fa-solid <?= ($_SESSION['msg_type'] === 'error') ? 'fa-circle-exclamation' : 'fa-circle-check' ?>" style="font-size: 1.25rem;"></i>
                    <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 800px;">
                <form action="<?= BASE_URL ?>/admin/exams/store" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label class="form-label">Paciente *</label>
                        <select name="patient_id" class="form-control" required>
                            <option value="">-- Selecione o paciente --</option>
                            <?php foreach($patients as $p): ?>
                                <option value="<?= $p['id'] ?>" data-company-id="<?= htmlspecialchars($p['default_company_id']) ?>"><?= htmlspecialchars($p['full_name']) ?> (CPF: <?= htmlspecialchars($p['cpf']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Origem do Atendimento *</label>
                            <select name="origin" id="originSelect" class="form-control" required>
                                <option value="private">Particular</option>
                                <option value="company">Encaminhado por Empresa</option>
                            </select>
                        </div>
                        <div class="form-group company-field" id="companyWrapper" style="flex: 1;">
                            <label class="form-label">Empresa Vinculada *</label>
                            <select name="company_id" id="companySelect" class="form-control">
                                <option value="">-- Selecione a empresa --</option>
                                <?php foreach($companies as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['trade_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 2;">
                            <label class="form-label">Tipo de Exame (Ex: ASO, Audiometria) *</label>
                            <input type="text" name="exam_type" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Data do Exame *</label>
                            <input type="text" name="exam_date" class="form-control" required value="<?= date('d/m/Y') ?>" placeholder="DD/MM/AAAA"
                                   oninput="this.value = this.value.replace(/\D/g, '').replace(/^(\d{2})(\d)/, '$1/$2').replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3').substring(0,10);">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Médico Responsável</label>
                        <input type="text" name="responsible_doctor" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Arquivo(s) do Exame (PDF ou Imagens) <small style="color: #64748b; font-weight: normal;">Pode selecionar vários de uma vez.</small></label>
                        <input type="file" name="exam_files[]" class="form-control" accept=".pdf,image/*" multiple>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observações Clínicas / Internas</label>
                        <textarea name="observations" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="allow_whatsapp" value="1" checked>
                            <span class="form-label" style="margin: 0;">Permitir notificação por WhatsApp quando disponível?</span>
                        </label>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">Salvar e Registrar Exame</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var originSelect = document.getElementById('originSelect');
    var companyWrapper = document.getElementById('companyWrapper');
    var companySelect = document.getElementById('companySelect');
    var patientSelect = document.querySelector('select[name="patient_id"]');
    var patientOptions = Array.from(patientSelect.options);

    function filterPatients() {
        var companyId = companySelect.value;
        var isCompanyOrigin = originSelect.value === 'company';
        var currentSelected = patientSelect.value;
        
        patientSelect.innerHTML = '';
        patientOptions.forEach(function(opt) {
            if (opt.value === '') {
                patientSelect.appendChild(opt.cloneNode(true)); // placeholder
                return;
            }
            // Se for particular, ou se a empresa não foi selecionada, ou se bate com a empresa
            // OU se o paciente já estava selecionado antes de mudar a empresa
            if (!isCompanyOrigin || !companyId || opt.getAttribute('data-company-id') === companyId || (currentSelected && opt.value === currentSelected)) {
                var newOpt = opt.cloneNode(true);
                if (currentSelected && newOpt.value === currentSelected) {
                    newOpt.selected = true;
                }
                patientSelect.appendChild(newOpt);
            }
        });
    }

    originSelect.addEventListener('change', function() {
        if (this.value === 'company') {
            companyWrapper.style.display = 'block';
            companySelect.setAttribute('required', 'required');
        } else {
            companyWrapper.style.display = 'none';
            companySelect.removeAttribute('required');
            companySelect.value = ''; // Limpa empresa
        }
        filterPatients();
    });

    companySelect.addEventListener('change', filterPatients);
});
</script>

</body>
</html>
