<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Portal do Paciente - MedWork</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .portal-header {
            background-color: var(--primary);
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
    <div style="font-size: 1.25rem; font-weight: bold;"><i class="fa-solid fa-heart-pulse"></i> MedWork</div>
    <div>
        <span style="margin-right: 1rem;">Olá, <?= htmlspecialchars($_SESSION['name']) ?></span>
        <a href="<?= BASE_URL ?>/logout" class="btn btn-secondary btn-sm" style="color: var(--text-main);">Sair</a>
    </div>
</header>

<main class="container" style="margin-top: 2rem;">
    <h1 style="margin-bottom: 1.5rem;">Meus Exames Ocupacionais</h1>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo de Exame</th>
                        <th>Empresa (se aplicável)</th>
                        <th>Médico</th>
                        <th>Protocolo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exams as $e): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($e['exam_date'])) ?></td>
                        <td><?= htmlspecialchars($e['exam_type']) ?></td>
                        <td><?= htmlspecialchars($e['company_name'] ?? 'Particular') ?></td>
                        <td><?= htmlspecialchars($e['responsible_doctor']) ?></td>
                        <td><?= htmlspecialchars($e['protocol_code']) ?></td>
                        <td>
                            <?php if(!empty($e['file_path'])): ?>
                                <a href="<?= BASE_URL ?>/portal/exam/view/<?= $e['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> Visualizar Exame</a>
                            <?php else: ?>
                                <span class="badge badge-warning">Apenas Resultado Textual</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($exams)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Nenhum exame liberado ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
