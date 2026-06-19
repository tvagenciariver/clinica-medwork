<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Especialidades - MedWork</title>
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
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 class="page-title"><i class="fa-solid fa-list" style="color: var(--primary); margin-right: 0.5rem;"></i> Especialidades / Setores</h1>
                
                <a href="<?= BASE_URL ?>/admin/specialties/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Nova Especialidade
                </a>
            </div>

            <?php if(isset($_SESSION['msg'])): ?>
                <div class="alert <?= ($_SESSION['msg_type'] === 'error') ? 'alert-danger' : 'alert-success' ?>" style="display: flex; align-items: center; gap: 0.75rem; border-left: 4px solid <?= ($_SESSION['msg_type'] === 'error') ? '#ef4444' : '#10b981' ?>; box-shadow: var(--shadow-sm); font-weight: 500;">
                    <i class="fa-solid <?= ($_SESSION['msg_type'] === 'error') ? 'fa-circle-exclamation' : 'fa-circle-check' ?>" style="font-size: 1.25rem;"></i>
                    <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cor</th>
                                <th>Nome da Especialidade</th>
                                <th style="text-align: right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($specialties)): ?>
                                <tr><td colspan="3" style="text-align: center; color: #64748b; padding: 2rem;">Nenhuma especialidade cadastrada.</td></tr>
                            <?php else: ?>
                                <?php foreach($specialties as $s): ?>
                                <tr>
                                    <td style="width: 60px;">
                                        <div style="width: 24px; height: 24px; border-radius: 4px; background-color: <?= htmlspecialchars($s['color_hex']) ?>;"></div>
                                    </td>
                                    <td style="font-weight: 500; color: #1e293b;">
                                        <?= htmlspecialchars($s['name']) ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= BASE_URL ?>/admin/specialties/edit/<?= $s['id'] ?>" class="btn btn-sm btn-secondary" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/specialties/delete/<?= $s['id'] ?>" class="btn btn-sm" style="background: #ef4444; color: white;" title="Excluir" onclick="return confirm('Deseja mesmo excluir esta especialidade?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
