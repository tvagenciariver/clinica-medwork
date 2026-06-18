<!-- src/Views/shared/sidebar.php -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-heart-pulse"></i> MedWork
    </div>
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-item">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <a href="<?= BASE_URL ?>/admin/exams" class="nav-item">
            <i class="fa-solid fa-file-medical"></i> Exames
        </a>
        <a href="<?= BASE_URL ?>/admin/patients" class="nav-item">
            <i class="fa-solid fa-users"></i> Pacientes
        </a>
        <a href="<?= BASE_URL ?>/admin/companies" class="nav-item">
            <i class="fa-solid fa-building"></i> Empresas
        </a>
        <a href="<?= BASE_URL ?>/admin/logs" class="nav-item">
            <i class="fa-solid fa-list-ol"></i> Logs de Envio
        </a>
        <a href="<?= BASE_URL ?>/admin/waha" class="nav-item">
            <i class="fa-brands fa-whatsapp"></i> Integração WAHA
        </a>
    </nav>
    <div style="padding: 1rem; border-top: 1px solid var(--border);">
        <a href="<?= BASE_URL ?>/logout" class="btn btn-secondary" style="width: 100%;">Sair</a>
    </div>
</aside>
