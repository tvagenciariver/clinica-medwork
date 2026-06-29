<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clínica Medicina do Trabalho</title>
    <!-- Incluindo fontawesome para icones via CDN caso necessário -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-brand" style="text-align: center; margin-bottom: 2rem;">
            <?php if (!empty($GLOBALS['appSettings']['company_logo'])): ?>
                <img src="<?= BASE_URL . $GLOBALS['appSettings']['company_logo'] ?>" alt="Logo" style="max-height: 80px; margin-bottom: 1rem; object-fit: contain;">
            <?php else: ?>
                <h1><i class="fa-solid fa-heart-pulse"></i> <?= htmlspecialchars($GLOBALS['appSettings']['company_name'] ?? 'MedWork') ?></h1>
            <?php endif; ?>
            <p style="color: #64748b; font-size: 0.95rem; margin-top: 0.5rem;">Sistema de Medicina Ocupacional</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/login" method="POST">
            <div class="form-group">
                <label for="email" class="form-label">E-mail ou Documento</label>
                <input type="text" id="email" name="email" class="form-control" placeholder="Seu e-mail cadastrado" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Senha</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Sua senha" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Entrar no Sistema</button>
        </form>
    </div>
</div>

</body>
</html>
