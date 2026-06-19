<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Portal da Empresa - MedWork</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .portal-header {
            background-color: #0f172a;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body style="background: var(--background);">

<header class="portal-header">
    <div style="font-size: 1.25rem; font-weight: bold;"><i class="fa-solid fa-building"></i> MedWork Corporate</div>
    <div>
        <span style="margin-right: 1rem;">Empresa: <?= htmlspecialchars($_SESSION['name']) ?></span>
        <a href="<?= BASE_URL ?>/logout" class="btn btn-secondary btn-sm" style="color: var(--text-main);">Sair</a>
    </div>
</header>

<main class="container" style="margin-top: 2rem;">
    <h1 style="margin-bottom: 1.5rem;">Exames dos Colaboradores</h1>

    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" action="<?= BASE_URL ?>/company/dashboard" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="flex: 2; margin: 0; min-width: 250px;">
                <label class="form-label">Buscar Colaborador (Nome/CPF)</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Digite sua busca...">
            </div>
            <div class="form-group" style="flex: 1; margin: 0; min-width: 150px;">
                <label class="form-label">Data Início</label>
                <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start ?? '') ?>">
            </div>
            <div class="form-group" style="flex: 1; margin: 0; min-width: 150px;">
                <label class="form-label">Data Fim</label>
                <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end ?? '') ?>">
            </div>
            <div class="form-group" style="flex: 1; margin: 0; min-width: 150px;">
                <label class="form-label">Tipo de Exame</label>
                <input type="text" name="exam_type" class="form-control" value="<?= htmlspecialchars($exam_type ?? '') ?>" placeholder="Ex: Audiometria">
            </div>
            <div style="margin-bottom: 0.25rem;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <?php if(!empty($search) || !empty($date_start) || !empty($date_end) || !empty($exam_type)): ?>
                    <a href="<?= BASE_URL ?>/company/dashboard" class="btn btn-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Colaborador</th>
                        <th>CPF</th>
                        <th>Tipo de Exame</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exams as $e): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($e['exam_date'])) ?></td>
                        <td><strong><?= htmlspecialchars($e['patient_name']) ?></strong></td>
                        <td><?= htmlspecialchars($e['cpf']) ?></td>
                        <td><?= htmlspecialchars($e['exam_type']) ?></td>
                        <td><span class="badge badge-success">Liberado</span></td>
                        <td>
                            <?php if(!empty($e['file_path'])): ?>
                                <a href="<?= BASE_URL ?>/portal/exam/view/<?= $e['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> Visualizar ASO</a>
                            <?php else: ?>
                                <span class="badge badge-default">Sem Arquivo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($exams)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Nenhum exame corporativo liberado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
