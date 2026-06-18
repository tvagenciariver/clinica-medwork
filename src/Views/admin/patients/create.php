<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Paciente - MedWork</title>
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
                <h1 class="page-title">Cadastrar Paciente</h1>
                <a href="<?= BASE_URL ?>/admin/patients" class="btn btn-secondary">Voltar</a>
            </div>

            <?php if(!empty($_SESSION['msg'])): ?>
                <div class="alert <?= ($_SESSION['msg_type'] === 'error') ? 'alert-danger' : 'alert-success' ?>" style="display: flex; align-items: center; gap: 0.75rem; border-left: 4px solid <?= ($_SESSION['msg_type'] === 'error') ? '#ef4444' : '#10b981' ?>; box-shadow: var(--shadow-sm); font-weight: 500;">
                    <i class="fa-solid <?= ($_SESSION['msg_type'] === 'error') ? 'fa-circle-exclamation' : 'fa-circle-check' ?>" style="font-size: 1.25rem;"></i>
                    <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 800px;">
                <form action="<?= BASE_URL ?>/admin/patients/store" method="POST">
                    
                    <div class="form-group">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">CPF</label>
                            <input type="text" name="cpf" class="form-control" required placeholder="Apenas números">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="text" name="birth_date" class="form-control" required placeholder="DD/MM/AAAA" 
                                   oninput="this.value = this.value.replace(/\D/g, '').replace(/^(\d{2})(\d)/, '$1/$2').replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3').substring(0,10);">
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Telefone (Celular)</label>
                            <input type="text" name="main_phone" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1; display: flex; align-items: center;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem; cursor: pointer;">
                                <input type="checkbox" name="has_whatsapp" value="1">
                                <span class="form-label" style="margin: 0;"><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> Este número tem WhatsApp?</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">E-mail (Usado para acesso ao portal do paciente. Senha será o CPF)</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Empresa Padrão (Deixe em branco se for particular)</label>
                        <select name="default_company_id" class="form-control">
                            <option value="">-- Particular --</option>
                            <?php foreach($companies as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['trade_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">Salvar Paciente</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>
