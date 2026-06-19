<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pacientes - MedWork</title>
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
                <h1 class="page-title">Pacientes</h1>
                <a href="<?= BASE_URL ?>/admin/patients/create" class="btn btn-primary"><i class="fa-solid fa-plus" style="margin-right: 0.5rem;"></i> Novo Paciente</a>
            </div>
            
            <div class="card" style="margin-bottom: 1.5rem;">
                <form method="GET" action="<?= BASE_URL ?>/admin/patients" style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label class="form-label">Buscar por Nome ou CPF</label>
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Digite o nome ou CPF...">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Buscar</button>
                        <?php if(!empty($search)): ?>
                            <a href="<?= BASE_URL ?>/admin/patients" class="btn btn-secondary">Limpar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome Completo</th>
                                <th>CPF</th>
                                <th>Telefone</th>
                                <th>WhatsApp?</th>
                                <th>Empresa Padrão</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($p['cpf']) ?></td>
                                <td><?= htmlspecialchars($p['main_phone']) ?></td>
                                <td>
                                    <?php if($p['has_whatsapp']): ?>
                                        <span class="badge badge-success"><i class="fa-brands fa-whatsapp"></i> Sim</span>
                                    <?php else: ?>
                                        <span class="badge badge-default">Não</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['company_name'] ?? 'Particular') ?></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
