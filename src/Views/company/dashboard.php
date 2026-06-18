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
                                <a href="<?= BASE_URL ?>/<?= $e['file_path'] ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-file-pdf"></i> Acessar ASO</a>
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
