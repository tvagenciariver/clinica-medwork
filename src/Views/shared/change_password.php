<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Trocar Senha - <?= htmlspecialchars($GLOBALS['appSettings']['company_name'] ?? 'Clínica') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body {
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-icon {
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <?php if(!empty($GLOBALS['appSettings']['company_logo'])): ?>
                <img src="<?= BASE_URL . $GLOBALS['appSettings']['company_logo'] ?>" alt="Logo" style="max-width: 150px; margin-bottom: 1rem;">
            <?php else: ?>
                <div class="brand-icon"><i class="fa-solid fa-hospital"></i></div>
            <?php endif; ?>
            <h1 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 0.5rem;">Defina sua Nova Senha</h1>
            <p style="color: #64748b; font-size: 0.9rem;">Por motivos de segurança, você precisa criar uma senha definitiva antes de acessar o painel.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem; text-align: center; border-radius: 6px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/portal/change-password/update" method="POST">
            <div class="form-group">
                <label class="form-label" for="password">Nova Senha</label>
                <div class="input-icon-wrapper" style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" style="padding-left: 2.5rem;" required minlength="6">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirmar Nova Senha</label>
                <div class="input-icon-wrapper" style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repita a senha" style="padding-left: 2.5rem;" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem; font-size: 1rem; margin-top: 1rem;">
                Salvar Senha e Entrar <i class="fa-solid fa-arrow-right ml-2"></i>
            </button>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="<?= BASE_URL ?>/logout" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">Sair</a>
            </div>
        </form>
    </div>
</body>
</html>
