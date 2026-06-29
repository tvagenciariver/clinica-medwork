<?php
$pageTitle = 'Importar Dados';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?> - <?= htmlspecialchars($appSettings['company_name'] ?? 'MedWork') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .upload-card {
            background: white;
            border-radius: 8px;
            padding: 2.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        .upload-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .stats-box {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 0.5rem;
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

            <div class="page-header" style="margin-bottom: 2rem; text-align: center;">
                <h1 class="page-title"><i class="fa-solid fa-file-excel" style="color: var(--primary); margin-right: 0.5rem;"></i> <?= $pageTitle ?></h1>
                <p style="color: #64748b;">Faça o upload de planilhas Excel (.xlsx ou .xls) para registrar exames em lote.</p>
            </div>

            <div class="upload-card">
                <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                <h2 style="margin-bottom: 1.5rem;">Selecione o arquivo Excel</h2>
                <form action="<?= BASE_URL ?>/admin/import/process" method="POST" enctype="multipart/form-data">
                    <input type="file" name="file" accept=".xlsx, .xls" class="form-control" style="margin-bottom: 1.5rem;" required>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 1.1rem; padding: 0.75rem;">
                        <i class="fa-solid fa-play"></i> Iniciar Importação
                    </button>
                </form>
                <div style="margin-top: 2rem; font-size: 0.85rem; color: #64748b; text-align: left; background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <strong>Regras do Robô:</strong><br><br>
                    <i class="fa-solid fa-check" style="color: #10b981;"></i> O robô usa a coluna O (CPF) para identificar pacientes. Sem CPF válido, a linha é ignorada.<br>
                    <i class="fa-solid fa-check" style="color: #10b981;"></i> O login inicial do paciente será o próprio CPF e ele será forçado a trocar de senha.<br>
                    <i class="fa-solid fa-check" style="color: #10b981;"></i> O sistema verifica se o exame já existe (usando CPF + Nome + Data). Dados duplicados são ignorados com segurança.
                </div>
            </div>

            <?php if(isset($import_stats)): ?>
                <div class="stats-box">
                    <div class="stat-card">
                        <div style="color: #64748b; font-size: 0.9rem;">Linhas Lidas</div>
                        <div class="stat-value" style="color: #3b82f6;"><?= $import_stats['total'] ?></div>
                    </div>
                    <div class="stat-card">
                        <div style="color: #64748b; font-size: 0.9rem;">Inseridos</div>
                        <div class="stat-value" style="color: #10b981;"><?= $import_stats['inserted'] ?></div>
                    </div>
                    <div class="stat-card">
                        <div style="color: #64748b; font-size: 0.9rem;">Duplicados / Ignorados</div>
                        <div class="stat-value" style="color: #f59e0b;"><?= $import_stats['skipped'] ?></div>
                    </div>
                    <div class="stat-card">
                        <div style="color: #64748b; font-size: 0.9rem;">Erros (Sem CPF, etc)</div>
                        <div class="stat-value" style="color: #ef4444;"><?= $import_stats['errors'] ?></div>
                    </div>
                </div>
                
                <?php if(!empty($import_debug)): ?>
                    <div style="margin-top: 1rem; background: #fee2e2; border: 1px solid #fca5a5; padding: 1rem; border-radius: 6px; color: #991b1b; font-size: 0.9rem;">
                        <strong>Debug (Primeiro Erro):</strong> <?= htmlspecialchars($import_debug) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>
