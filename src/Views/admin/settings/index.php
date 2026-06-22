<?php
$pageTitle = 'Configurações Gerais';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?> - <?= htmlspecialchars($appSettings['company_name'] ?? 'MedWork') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .settings-card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .settings-section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
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
                <div class="alert <?= ($msg_type === 'error') ? 'alert-danger' : 'alert-success' ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="page-header" style="margin-bottom: 2rem;">
                <h1 class="page-title"><i class="fa-solid fa-gear" style="color: var(--primary); margin-right: 0.5rem;"></i> <?= $pageTitle ?></h1>
                <p style="color: #64748b;">Personalize a identidade e dados da sua clínica.</p>
            </div>

            <form action="<?= BASE_URL ?>/admin/settings/update" method="POST" enctype="multipart/form-data">
                
                <!-- IDENTIDADE VISUAL -->
                <div class="settings-card">
                    <h2 class="settings-section-title">Identidade Visual</h2>
                    
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                        <div>
                            <label class="form-label">Logomarca Atual</label>
                            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; text-align: center; margin-bottom: 1rem;">
                                <?php if(!empty($settings['company_logo'])): ?>
                                    <img src="<?= BASE_URL . $settings['company_logo'] ?>" alt="Logo" style="max-width: 100%; max-height: 100px; object-fit: contain;">
                                <?php else: ?>
                                    <i class="fa-solid fa-hospital" style="font-size: 3rem; color: #94a3b8;"></i>
                                    <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">Nenhuma logo enviada.</p>
                                <?php endif; ?>
                            </div>
                            <label class="form-label">Nova Logomarca (PNG, JPG)</label>
                            <input type="file" name="company_logo" class="form-control" accept="image/png, image/jpeg, image/webp">
                        </div>
                        
                        <div>
                            <div class="form-group">
                                <label class="form-label">Nome da Clínica / Empresa</label>
                                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name'] ?? 'MedWork') ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DADOS DA CLÍNICA -->
                <div class="settings-card">
                    <h2 class="settings-section-title">Dados de Contato e Faturamento</h2>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">CNPJ</label>
                            <input type="text" name="company_cnpj" class="form-control" value="<?= htmlspecialchars($settings['company_cnpj'] ?? '') ?>" placeholder="00.000.000/0000-00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Telefone Principal</label>
                            <input type="text" name="company_phone" class="form-control" value="<?= htmlspecialchars($settings['company_phone'] ?? '') ?>" placeholder="(00) 00000-0000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">E-mail de Contato</label>
                        <input type="email" name="company_email" class="form-control" value="<?= htmlspecialchars($settings['company_email'] ?? '') ?>" placeholder="contato@clinica.com.br">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Endereço Completo</label>
                        <textarea name="company_address" class="form-control" rows="2" placeholder="Rua, Número, Bairro, Cidade - Estado"><?= htmlspecialchars($settings['company_address'] ?? '') ?></textarea>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-bottom: 3rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1.1rem;">
                        <i class="fa-solid fa-save"></i> Salvar Configurações
                    </button>
                </div>

            </form>
        </div>
    </main>
</div>

</body>
</html>
