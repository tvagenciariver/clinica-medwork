<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MedWork</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="app-layout">
    <?php include __DIR__ . '/../shared/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div>
                <!-- Busca global futuramente aqui -->
            </div>
            <div class="user-profile flex items-center gap-2">
                <span class="badge badge-info"><?= htmlspecialchars($_SESSION['role'] === 'admin' ? 'Administrador' : 'Funcionário') ?></span>
                <span style="font-weight: 500;"><?= htmlspecialchars($_SESSION['name']) ?></span>
            </div>
        </header>

        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <a href="<?= BASE_URL ?>/admin/exams/create" class="btn btn-primary"><i class="fa-solid fa-plus" style="margin-right: 0.5rem;"></i> Novo Exame</a>
            </div>

            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <div class="stat-card">
                    <span class="stat-title">Total de Exames</span>
                    <span class="stat-value"><?= $totalExams ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Confirmados / Prontos</span>
                    <span class="stat-value" style="color: #10b981;"><?= $availableExams ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Pendentes / Aguardando</span>
                    <span class="stat-value" style="color: #f59e0b;"><?= $pendingExams ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Cancelados</span>
                    <span class="stat-value" style="color: #ef4444;"><?= $cancelledExams ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Pacientes</span>
                    <span class="stat-value" style="color: #3b82f6;"><?= $totalPatients ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Empresas</span>
                    <span class="stat-value" style="color: #6366f1;"><?= $totalCompanies ?></span>
                </div>
            </div>

            <div class="card">
                <h2 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Exames Recentes</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Protocolo</th>
                                <th>Paciente</th>
                                <th>Tipo de Exame</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentExams as $exam): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($exam['protocol_code']) ?></strong></td>
                                <td><?= htmlspecialchars($exam['patient']) ?></td>
                                <td><?= htmlspecialchars($exam['exam_type']) ?></td>
                                <td>
                                    <?php if($exam['status'] === 'registered'): ?>
                                        <span class="badge badge-default">Cadastrado</span>
                                    <?php elseif($exam['status'] === 'available'): ?>
                                        <span class="badge badge-warning">Disponível</span>
                                    <?php elseif($exam['status'] === 'sent_whatsapp'): ?>
                                        <span class="badge badge-success">Enviado WhatsApp</span>
                                    <?php elseif($exam['status'] === 'viewed_company'): ?>
                                        <span class="badge" style="background: #e0e7ff; color: #3730a3;">Visualizado pela Empresa</span>
                                    <?php elseif($exam['status'] === 'viewed_patient'): ?>
                                        <span class="badge" style="background: #e0e7ff; color: #3730a3;">Visualizado pelo Paciente</span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><?= htmlspecialchars($exam['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($exam['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recentExams)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">Nenhum exame cadastrado ainda.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
