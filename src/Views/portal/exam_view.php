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

<main class="viewer-container" style="display: flex; flex-direction: column; justify-content: flex-start; gap: 2rem; align-items: center; padding: 2rem 1rem;">
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
            <div style="width: 100%; display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem;">
                <?php if ($isImage): ?>
                    <div style="display: flex; align-items: center; background: #f1f5f9; border-radius: 6px; padding: 0.25rem; border: 1px solid #cbd5e1;">
                        <button onclick="changeZoom('img-<?= $index ?>', -0.5)" class="btn btn-sm" style="background: transparent; color: #333; padding: 0.25rem 0.5rem;" title="Reduzir">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <span id="zoom-level-img-<?= $index ?>" style="font-size: 0.85rem; font-weight: 600; min-width: 40px; text-align: center; display: inline-block;">1x</span>
                        <button onclick="changeZoom('img-<?= $index ?>', 0.5)" class="btn btn-sm" style="background: transparent; color: #333; padding: 0.25rem 0.5rem;" title="Ampliar">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/<?= htmlspecialchars($path) ?>" download class="btn btn-primary btn-sm"><i class="fa-solid fa-download"></i> Baixar Arquivo <?= count($paths) > 1 ? ($index + 1) : '' ?></a>
            </div>
            
            <?php if ($isImage): ?>
                <div class="drag-container" id="drag-container-<?= $index ?>" style="width: 100%; overflow: auto; text-align: center; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; background: #fff; max-height: 80vh; cursor: grab;">
                    <img id="img-<?= $index ?>" src="<?= BASE_URL ?>/<?= htmlspecialchars($path) ?>" style="width: 100%; max-width: none; transition: width 0.2s ease; pointer-events: none;" data-zoom="1">
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
function changeZoom(imgId, delta) {
    var img = document.getElementById(imgId);
    var label = document.getElementById('zoom-level-' + imgId);
    if (!img || !label) return;

    var currentZoom = parseFloat(img.getAttribute('data-zoom')) || 1;
    var newZoom = currentZoom + delta;
    
    // Limites de zoom: 0.5x até 5x
    if (newZoom < 0.5) newZoom = 0.5;
    if (newZoom > 5) newZoom = 5;
    
    img.setAttribute('data-zoom', newZoom);
    label.innerText = newZoom + 'x';
    
    // Alterando o tamanho real da imagem para que o container crie a barra de rolagem corretamente
    img.style.maxWidth = 'none';
    img.style.width = (newZoom * 100) + '%';
    img.style.height = 'auto';
}

// Suporte para arrastar (pan) a imagem e clique duplo
document.querySelectorAll('.drag-container').forEach(function(slider) {
    let isDown = false;
    let startX;
    let startY;
    let scrollLeft;
    let scrollTop;

    slider.addEventListener('mousedown', (e) => {
        isDown = true;
        slider.style.cursor = 'grabbing';
        startX = e.pageX - slider.offsetLeft;
        startY = e.pageY - slider.offsetTop;
        scrollLeft = slider.scrollLeft;
        scrollTop = slider.scrollTop;
    });

    slider.addEventListener('mouseleave', () => {
        isDown = false;
        slider.style.cursor = 'grab';
    });

    slider.addEventListener('mouseup', () => {
        isDown = false;
        slider.style.cursor = 'grab';
    });

    slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const y = e.pageY - slider.offsetTop;
        const walkX = (x - startX) * 2; // Velocidade de arraste
        const walkY = (y - startY) * 2;
        slider.scrollLeft = scrollLeft - walkX;
        slider.scrollTop = scrollTop - walkY;
    });

    // Clique duplo para resetar zoom (pegando o ID da imagem filha)
    slider.addEventListener('dblclick', function() {
        var img = this.querySelector('img');
        if (img) {
            var currentZoom = parseFloat(img.getAttribute('data-zoom')) || 1;
            if (currentZoom >= 2) {
                changeZoom(img.id, 1 - currentZoom); // reseta para 1x
            } else {
                changeZoom(img.id, 1); // incrementa em 1x
            }
        }
    });
});
</script>

</body>
</html>
