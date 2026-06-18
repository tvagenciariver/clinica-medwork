<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Logs WAHA - MedWork</title>
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
                <h1 class="page-title">Histórico de Disparos WhatsApp</h1>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Protocolo (Exame)</th>
                                <th>Destinatário</th>
                                <th>Número/ChatId</th>
                                <th>Disparado por</th>
                                <th>Status</th>
                                <th>Retorno API</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $l): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i:s', strtotime($l['sent_at'])) ?></td>
                                <td><?= htmlspecialchars($l['protocol_code']) ?></td>
                                <td>
                                    <?php if($l['recipient_type'] === 'patient'): ?>
                                        <i class="fa-solid fa-user"></i> <?= htmlspecialchars($l['patient_name']) ?>
                                    <?php else: ?>
                                        <i class="fa-solid fa-building"></i> <?= htmlspecialchars($l['company_name']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($l['destination_phone']) ?></td>
                                <td><?= htmlspecialchars($l['sent_by_name']) ?></td>
                                <td>
                                    <?php if($l['status'] === 'success'): ?>
                                        <span class="badge badge-success">Sucesso</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Erro</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="alert('Retorno: <?= htmlspecialchars(addslashes($l['api_response'])) ?>')" class="btn btn-secondary btn-sm">Ver Payload</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($logs)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">Nenhum disparo de WhatsApp realizado ainda.</td>
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
