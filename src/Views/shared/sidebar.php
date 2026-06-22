<!-- src/Views/shared/sidebar.php -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <?php $settings = $GLOBALS['appSettings'] ?? []; ?>
        <?php if(!empty($settings['company_logo'])): ?>
            <img src="<?= BASE_URL . $settings['company_logo'] ?>" alt="Logo" style="max-width: 100%; max-height: 40px; object-fit: contain;">
        <?php else: ?>
            <div class="brand-icon"><i class="fa-solid fa-hospital"></i></div>
            <span><?= htmlspecialchars($settings['company_name'] ?? 'MedWork') ?></span>
        <?php endif; ?>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-item">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <a href="<?= BASE_URL ?>/admin/exams" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/exams') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-file-medical"></i> Exames e Laudos
        </a>
        <a href="<?= BASE_URL ?>/admin/appointments" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/appointments') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-check"></i> Agenda Kanban
        </a>
        <a href="<?= BASE_URL ?>/admin/specialties" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/specialties') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-list"></i> Especialidades
        </a>
        <a href="<?= BASE_URL ?>/admin/patients" class="nav-item">
            <i class="fa-solid fa-users"></i> Pacientes
        </a>
        <a href="<?= BASE_URL ?>/admin/companies" class="nav-item">
            <i class="fa-solid fa-building"></i> Empresas
        </a>
        <a href="<?= BASE_URL ?>/admin/settings" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-gear"></i> Configurações
        </a>
        <a href="<?= BASE_URL ?>/admin/users" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-users-cog"></i> Usuários
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
