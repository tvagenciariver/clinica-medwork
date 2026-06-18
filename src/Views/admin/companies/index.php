<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Empresas - MedWork</title>
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
                <h1 class="page-title">Empresas Parceiras</h1>
                <a href="<?= BASE_URL ?>/admin/companies/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nova Empresa</a>
            </div>

            <?php if(!empty($msg)): ?>
                <div class="alert <?= ($_SESSION['msg_type'] ?? 'success') === 'error' ? 'alert-danger' : 'alert-success' ?>" style="background: <?= ($_SESSION['msg_type'] ?? 'success') === 'error' ? '#fef2f2' : '#d1fae5' ?>; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome Fantasia</th>
                                <th>CNPJ</th>
                                <th>Responsável</th>
                                <th>Telefone</th>
                                <th>WhatsApp</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $c): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($c['trade_name'] ?: $c['corporate_name']) ?></strong></td>
                                <td><?= htmlspecialchars($c['cnpj']) ?></td>
                                <td><?= htmlspecialchars($c['manager_name']) ?></td>
                                <td><?= htmlspecialchars($c['main_phone']) ?></td>
                                <td>
                                    <?php if($c['has_whatsapp']): ?>
                                        <span class="badge badge-success"><i class="fa-brands fa-whatsapp"></i> Sim</span>
                                    <?php else: ?>
                                        <span class="badge badge-default">Não</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($c['status'] === 'active'): ?>
                                        <span class="badge badge-success">Ativa</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inativa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($companies)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">Nenhuma empresa cadastrada.</td>
                                </tr>
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
