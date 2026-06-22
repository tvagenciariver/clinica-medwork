<?php
$pageTitle = 'Controle de Usuários';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?> - <?= htmlspecialchars($appSettings['company_name'] ?? 'MedWork') ?></title>
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
            <?php if(isset($msg)): ?>
                <div class="alert <?= ($msg_type === 'error') ? 'alert-danger' : 'alert-success' ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 class="page-title"><i class="fa-solid fa-users-cog" style="color: var(--primary); margin-right: 0.5rem;"></i> <?= $pageTitle ?></h1>
                
                <a href="<?= BASE_URL ?>/admin/users/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Novo Usuário
                </a>
            </div>

            <div class="card p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail (Login)</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Cadastro</th>
                            <th style="width: 150px; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td style="font-weight: 500; color: #1e293b;"><?= htmlspecialchars($u['name']) ?></td>
                                    <td style="color: #64748b;"><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php if($u['role'] === 'admin'): ?>
                                            <span class="badge badge-primary">Administrador</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Funcionário</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($u['status'] === 'active'): ?>
                                            <span style="color: #10b981; font-weight: 500;"><i class="fa-solid fa-circle-check"></i> Ativo</span>
                                        <?php else: ?>
                                            <span style="color: #ef4444; font-weight: 500;"><i class="fa-solid fa-ban"></i> Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: #64748b; font-size: 0.875rem;">
                                        <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="<?= BASE_URL ?>/admin/users/edit/<?= $u['id'] ?>" class="btn btn-sm btn-secondary" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <?php if($u['id'] !== $_SESSION['user_id']): ?>
                                            <a href="<?= BASE_URL ?>/admin/users/delete/<?= $u['id'] ?>" class="btn btn-sm" style="background: #ef4444; color: white;" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir o usuário <?= htmlspecialchars(addslashes($u['name'])) ?>?');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: #64748b;">Nenhum usuário encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>

</body>
</html>
