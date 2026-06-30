<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurações WAHA API - MedWork</title>
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
            <div class="page-header">
                <h1 class="page-title"><i class="fa-brands fa-whatsapp" style="color: #25D366; margin-right: 0.5rem;"></i> Integração WAHA API</h1>
            </div>

            <?php if(!empty($msg)): ?>
                <div class="alert <?= ($msg_type === 'error') ? 'alert-danger' : 'alert-success' ?>" style="display: flex; align-items: center; gap: 0.75rem; border-left: 4px solid <?= ($msg_type === 'error') ? '#ef4444' : '#10b981' ?>; box-shadow: var(--shadow-sm); font-weight: 500;">
                    <i class="fa-solid <?= ($msg_type === 'error') ? 'fa-circle-exclamation' : 'fa-circle-check' ?>" style="font-size: 1.25rem;"></i>
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 800px;">
                <form action="<?= BASE_URL ?>/admin/waha/store" method="POST">
                    <h3 style="margin-bottom: 1rem; color: var(--primary);">Conexão da API</h3>
                    
                    <div class="form-group">
                        <label class="form-label">URL Base da WAHA (ex: http://localhost:3000)</label>
                        <input type="text" name="waha_base_url" class="form-control" value="<?= htmlspecialchars($settings['waha_base_url'] ?? '') ?>" required>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Nome da Sessão (ex: default)</label>
                            <input type="text" name="waha_session" class="form-control" value="<?= htmlspecialchars($settings['waha_session'] ?? 'default') ?>" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">API Key (opcional se não usar Auth)</label>
                            <input type="text" name="waha_api_key" class="form-control" value="<?= htmlspecialchars($settings['waha_api_key'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label class="form-label">Template Mensagem para Agendamentos (Usado ao confirmar consultas)</label>
                        <small style="color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                            Variáveis: <code>{paciente}</code>, <code>{procedimento}</code>, <code>{data}</code>, <code>{hora}</code>, <code>{clinica}</code>
                        </small>
                        <textarea name="waha_template_appointment" class="form-control" rows="3" required><?= htmlspecialchars($settings['waha_template_appointment'] ?? "Olá, {paciente}. Você tem uma consulta de {procedimento} agendada para {data} às {hora} na {clinica}. Responda SIM para confirmar ou NÃO para cancelar.") ?></textarea>
                    </div>

                    <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

                    <h3 style="margin-bottom: 1rem; color: var(--primary);">Modelos de Mensagem (Templates)</h3>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
                        Você pode utilizar as seguintes variáveis nas mensagens:<br>
                        <code>{paciente}</code> - Nome do Paciente<br>
                        <code>{protocolo}</code> - Protocolo do Exame<br>
                        <code>{clinica}</code> - Nome fixo da Clínica<br>
                        <code>{url}</code> - Link do Portal de Acesso
                    </p>

                    <div class="form-group">
                        <label class="form-label">Mensagem para o Paciente</label>
                        <textarea name="waha_template_patient" class="form-control" rows="3" required><?= htmlspecialchars($settings['waha_template_patient'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mensagem para a Empresa</label>
                        <textarea name="waha_template_company" class="form-control" rows="3" required><?= htmlspecialchars($settings['waha_template_company'] ?? '') ?></textarea>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save" style="margin-right: 0.5rem;"></i> Salvar Configurações</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>
