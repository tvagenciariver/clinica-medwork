<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Exames - MedWork</title>
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
                <h1 class="page-title">Gerenciar Exames</h1>
                <a href="<?= BASE_URL ?>/admin/exams/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Registrar Exame</a>
            </div>

            <?php if(!empty($msg)): ?>
                <div class="alert <?= ($_SESSION['msg_type'] ?? 'success') === 'error' ? 'alert-danger' : 'alert-success' ?>" style="background: <?= ($_SESSION['msg_type'] ?? 'success') === 'error' ? '#fef2f2' : '#d1fae5' ?>; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Protocolo</th>
                                <th>Paciente</th>
                                <th>Origem</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Ações WAHA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exams as $e): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($e['protocol_code']) ?></strong></td>
                                <td><?= htmlspecialchars($e['patient_name']) ?></td>
                                <td>
                                    <?php if($e['origin'] === 'company'): ?>
                                        <i class="fa-solid fa-building" title="Empresa"></i> <?= htmlspecialchars($e['company_name']) ?>
                                    <?php else: ?>
                                        <i class="fa-solid fa-user" title="Particular"></i> Particular
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($e['exam_type']) ?></td>
                                <td>
                                    <?php if($e['status'] === 'registered'): ?>
                                        <span class="badge badge-default">Cadastrado</span>
                                    <?php elseif($e['status'] === 'available'): ?>
                                        <span class="badge badge-warning">Disponível</span>
                                    <?php elseif($e['status'] === 'sent_whatsapp'): ?>
                                        <span class="badge badge-success">Notificado WP</span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><?= $e['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($e['status'] === 'registered' || $e['status'] === 'processing'): ?>
                                        <a href="<?= BASE_URL ?>/admin/exams/makeAvailable?id=<?= $e['id'] ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-check"></i> Marcar Disponível</a>
                                    <?php elseif($e['status'] === 'available' || $e['status'] === 'sent_whatsapp'): ?>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="<?= BASE_URL ?>/admin/exams/sendWaha?id=<?= $e['id'] ?>&target=patient" class="btn btn-sm" style="background: #25D366; color: white; padding: 0.5rem;"><i class="fa-brands fa-whatsapp"></i> Paciente</a>
                                            
                                            <?php if($e['origin'] === 'company'): ?>
                                                <a href="<?= BASE_URL ?>/admin/exams/sendWaha?id=<?= $e['id'] ?>&target=company" class="btn btn-sm" style="background: #128C7E; color: white; padding: 0.5rem;"><i class="fa-brands fa-whatsapp"></i> Empresa</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
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
