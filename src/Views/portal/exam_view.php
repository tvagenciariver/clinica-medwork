<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualizador de Exame - MedWork</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body { background: var(--background); margin: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .viewer-header {
            background-color: #0f172a; color: white; padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;
        }
        .viewer-container {
            flex-grow: 1; padding: 1rem; background: #e2e8f0;
            display: flex; justify-content: center; align-items: flex-start;
            overflow: auto;
        }
        .file-frame {
            width: 100%; height: calc(100vh - 100px); max-width: 1200px;
            border: none; background: white; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>

<header class="viewer-header">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem;">Exame de <?= htmlspecialchars($exam['patient_name']) ?></h2>
        <span style="font-size: 0.85rem; color: #94a3b8;"><?= htmlspecialchars($exam['exam_type']) ?> - <?= date('d/m/Y', strtotime($exam['exam_date'])) ?></span>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($exam['file_path']) ?>" download class="btn btn-primary"><i class="fa-solid fa-download"></i> Baixar Arquivo Original</a>
    </div>
</header>

<main class="viewer-container">
    <?php
    $ext = strtolower(pathinfo($exam['file_path'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
    ?>
        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($exam['file_path']) ?>" style="max-width: 100%; height: auto; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border-radius: 8px; margin: auto;">
    <?php else: ?>
        <iframe src="<?= BASE_URL ?>/<?= htmlspecialchars($exam['file_path']) ?>" class="file-frame"></iframe>
    <?php endif; ?>
</main>

</body>
</html>
