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
                <h1 class="page-title">Empresas (Corporate)</h1>
                <a href="<?= BASE_URL ?>/admin/companies/create" class="btn btn-primary"><i class="fa-solid fa-plus" style="margin-right: 0.5rem;"></i> Nova Empresa</a>
            </div>

            <?php if(!empty($_SESSION['msg'])): ?>
                <div class="alert <?= ($_SESSION['msg_type'] === 'error') ? 'alert-danger' : 'alert-success' ?>" style="display: flex; align-items: center; gap: 0.75rem; border-left: 4px solid <?= ($_SESSION['msg_type'] === 'error') ? '#ef4444' : '#10b981' ?>; box-shadow: var(--shadow-sm); font-weight: 500;">
                    <i class="fa-solid <?= ($_SESSION['msg_type'] === 'error') ? 'fa-circle-exclamation' : 'fa-circle-check' ?>" style="font-size: 1.25rem;"></i>
                    <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
                </div>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 1.5rem;">
                <form method="GET" action="<?= BASE_URL ?>/admin/companies" style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label class="form-label">Buscar por Razão Social, Nome Fantasia ou CNPJ</label>
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Digite sua busca...">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Buscar</button>
                        <?php if(!empty($search)): ?>
                            <a href="<?= BASE_URL ?>/admin/companies" class="btn btn-secondary">Limpar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

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
                                <th>Ações</th>
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
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/companies/edit/<?= $c['id'] ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i> Editar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($companies)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">Nenhuma empresa cadastrada.</td>
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
