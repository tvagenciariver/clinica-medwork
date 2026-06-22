<?php
$pageTitle = 'Editar Usuário';
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
            <div>
                <a href="<?= BASE_URL ?>/admin/users" style="color: #64748b; text-decoration: none; font-weight: 500;">
                    <i class="fa-solid fa-arrow-left"></i> Voltar
                </a>
            </div>
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
                <h1 class="page-title"><i class="fa-solid fa-user-pen" style="color: var(--primary); margin-right: 0.5rem;"></i> <?= $pageTitle ?></h1>
            </div>

            <div class="card" style="max-width: 600px;">
                <form action="<?= BASE_URL ?>/admin/users/update/<?= $user['id'] ?>" method="POST">
                    
                    <div class="form-group">
                        <label class="form-label">Nome Completo <span style="color: red;">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">E-mail (Login) <span style="color: red;">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" name="password" class="form-control" placeholder="******">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Perfil de Acesso</label>
                            <select name="role" class="form-control" <?= ($user['id'] === $_SESSION['user_id']) ? 'disabled' : '' ?>>
                                <option value="employee" <?= $user['role'] === 'employee' ? 'selected' : '' ?>>Funcionário</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrador Master</option>
                            </select>
                            <?php if($user['id'] === $_SESSION['user_id']): ?>
                                <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
                                <small style="color: #64748b;">Você não pode mudar seu próprio perfil.</small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" <?= ($user['id'] === $_SESSION['user_id']) ? 'disabled' : '' ?>>
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Ativo (Pode logar)</option>
                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inativo (Bloqueado)</option>
                            </select>
                            <?php if($user['id'] === $_SESSION['user_id']): ?>
                                <input type="hidden" name="status" value="<?= htmlspecialchars($user['status']) ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            <i class="fa-solid fa-save"></i> Salvar Alterações
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>
</div>

</body>
</html>
