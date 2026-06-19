<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Especialidade - MedWork</title>
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
                <h1 class="page-title">Nova Especialidade</h1>
                <a href="<?= BASE_URL ?>/admin/specialties" class="btn btn-secondary">Voltar</a>
            </div>

            <?php if(!empty($_SESSION['msg'])): ?>
                <div class="alert <?= ($_SESSION['msg_type'] === 'error') ? 'alert-danger' : 'alert-success' ?>">
                    <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px;">
                <form action="<?= BASE_URL ?>/admin/specialties/store" method="POST">
                    
                    <div class="form-group">
                        <label class="form-label">Nome da Especialidade / Setor *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Clínica Geral">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cor de Identificação no Kanban *</label>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <input type="color" name="color_hex" value="#3b82f6" style="width: 50px; height: 50px; border: none; border-radius: 4px; cursor: pointer; padding: 0;">
                            <span style="color: var(--text-muted); font-size: 0.875rem;">A cor será usada como tag visual no painel.</span>
                        </div>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">Salvar Especialidade</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
