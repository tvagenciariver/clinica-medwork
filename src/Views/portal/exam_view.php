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
    </div>
</header>

<main class="viewer-container" style="display: flex; flex-direction: column; gap: 2rem; align-items: center; padding: 2rem 1rem;">
    <?php
    $paths = [];
    if (!empty($exam['file_path'])) {
        $decoded = json_decode($exam['file_path'], true);
        if (is_array($decoded)) {
            $paths = $decoded;
        } else {
            $paths = [$exam['file_path']]; // Compatibilidade com exames antigos (string simples)
        }
    }

    foreach ($paths as $index => $path):
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    ?>
        <div style="width: 100%; max-width: 1200px; display: flex; flex-direction: column; align-items: center; gap: 1rem;">
            <div style="width: 100%; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <?php if ($isImage): ?>
                    <button onclick="toggleZoom('img-<?= $index ?>')" class="btn btn-secondary btn-sm" title="Clique para dar Zoom">
                        <i class="fa-solid fa-magnifying-glass-plus"></i> Zoom na Imagem
                    </button>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/<?= htmlspecialchars($path) ?>" download class="btn btn-primary btn-sm"><i class="fa-solid fa-download"></i> Baixar Arquivo <?= count($paths) > 1 ? ($index + 1) : '' ?></a>
            </div>
            
            <?php if ($isImage): ?>
                <div style="width: 100%; overflow: auto; text-align: center; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; background: #fff;">
                    <img id="img-<?= $index ?>" src="<?= BASE_URL ?>/<?= htmlspecialchars($path) ?>" style="max-width: 100%; height: auto; cursor: zoom-in; transition: all 0.3s ease;">
                </div>
            <?php else: ?>
                <iframe src="<?= BASE_URL ?>/<?= htmlspecialchars($path) ?>" style="width: 100%; height: 800px; border: none; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);"></iframe>
            <?php endif; ?>
        </div>
        
        <?php if ($index < count($paths) - 1): ?>
            <hr style="width: 100%; border: none; border-top: 2px dashed #cbd5e1; margin: 1rem 0;">
        <?php endif; ?>
        
    <?php endforeach; ?>

    <?php if (empty($paths)): ?>
        <p>Nenhum arquivo anexado a este exame.</p>
    <?php endif; ?>
</main>

<script>
function toggleZoom(imgId) {
    var img = document.getElementById(imgId);
    if (img.style.maxWidth === '100%') {
        img.style.maxWidth = 'none';
        img.style.cursor = 'zoom-out';
    } else {
        img.style.maxWidth = '100%';
        img.style.cursor = 'zoom-in';
    }
}

// Add click listener to image directly too
document.querySelectorAll('img[id^="img-"]').forEach(function(img) {
    img.addEventListener('click', function() {
        toggleZoom(this.id);
    });
});
</script>

</body>
</html>
