<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    <?php
    // Injection dynamique de la couleur primaire du thème
    $themeCouleur = ParametresController::get('theme_couleur_primaire', '#0a2342');
    if ($themeCouleur && preg_match('/^#[0-9a-fA-F]{3,6}$/', $themeCouleur)):
    ?>
    <style>
    :root {
        --navy-dark: <?= htmlspecialchars($themeCouleur) ?>;
        --navy:      <?= htmlspecialchars($themeCouleur) ?>;
    }
    .btn-primary { background-color: <?= htmlspecialchars($themeCouleur) ?>; border-color: <?= htmlspecialchars($themeCouleur) ?>; }
    .btn-primary:hover { filter: brightness(1.15); background-color: <?= htmlspecialchars($themeCouleur) ?>; border-color: <?= htmlspecialchars($themeCouleur) ?>; }
    .text-primary { color: <?= htmlspecialchars($themeCouleur) ?> !important; }
    .bg-primary { background-color: <?= htmlspecialchars($themeCouleur) ?> !important; }
    .border-primary { border-color: <?= htmlspecialchars($themeCouleur) ?> !important; }
    </style>
    <?php endif; ?>
</head>
<body>
<?php
$currentUser  = Auth::currentUser();
$alerteHelper = new AlerteHelper(Database::getInstance()->getPDO());
$nbAlertes    = $alerteHelper->countUnread($currentUser['id'] ?? null);
$currentPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($scriptDir && str_starts_with($currentPath, $scriptDir)) {
    $currentPath = substr($currentPath, strlen($scriptDir));
}
$currentPath = '/' . ltrim($currentPath, '/');

function isActive(string $prefix, string $currentPath): string {
    return str_starts_with($currentPath, $prefix) ? 'active' : '';
}
$_uid = (int)($currentUser['id'] ?? 0);
// Helper : menu visible si l'utilisateur a le droit ET le rôle (admin bypass total)
$canMenu = function(string $code) use ($_uid): bool {
    if (Auth::hasRole(['admin'])) return true;
    return DroitsController::hasMenuAccess($_uid, $code);
};
?>

<!-- Overlay mobile (ferme sidebar) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ═══════════════════════════════════ SIDEBAR ═══════════════════════════════════ -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-balance-scale"></i>
        <div>
            <div class="fw-bold" style="font-size:0.87rem">TGI-NY</div>
            <div style="font-size:0.68rem;opacity:0.65;line-height:1.3">Tribunal de Grande Instance<br>Hors Classe de Niamey</div>
        </div>
    </div>
    <nav class="sidebar-nav mt-2">
        <?php if ($canMenu('dashboard')): ?>
        <a href="<?= BASE_URL ?>/dashboard" class="sidebar-link <?= isActive('/dashboard', $currentPath) ?: ($currentPath === '/' ? 'active' : '') ?>">
            <i class="bi bi-speedometer2"></i><span>Tableau de bord</span>
        </a>
        <?php endif; ?>

        <?php
        $showParquet = $canMenu('pv') || $canMenu('avocats') || $canMenu('casier_judiciaire') || $canMenu('plaintes');
        if ($showParquet):
        ?>
        <!-- ══════════ PARQUET ══════════ -->
        <div class="sidebar-section">Parquet</div>
        <?php if ($canMenu('pv')): ?>
        <a href="<?= BASE_URL ?>/pv" class="sidebar-link <?= isActive('/pv', $currentPath) ?>">
            <i class="bi bi-file-text"></i><span>Procès-Verbaux</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('plaintes')): ?>
        <a href="<?= BASE_URL ?>/plaintes" class="sidebar-link <?= isActive('/plaintes', $currentPath) ?>">
            <i class="bi bi-megaphone"></i><span>Plaintes</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('avocats')): ?>
        <a href="<?= BASE_URL ?>/avocats" class="sidebar-link <?= isActive('/avocats', $currentPath) ?>">
            <i class="bi bi-person-badge"></i><span>Barreau / Avocats</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('casier_judiciaire')): ?>
        <a href="<?= BASE_URL ?>/casier-judiciaire" class="sidebar-link <?= isActive('/casier-judiciaire', $currentPath) ?>">
            <i class="bi bi-person-vcard"></i><span>Casier judiciaire</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php
        $showInstruction = $canMenu('dossiers') || $canMenu('ordonnances') || $canMenu('controles_judiciaires') || $canMenu('expertises') || $canMenu('commissions_rogatoires') || $canMenu('scelles');
        if ($showInstruction):
        ?>
        <!-- ══════════ CABINET D'INSTRUCTION ══════════ -->
        <div class="sidebar-section">Cabinet d'Instruction</div>
        <?php if ($canMenu('dossiers')): ?>
        <a href="<?= BASE_URL ?>/dossiers" class="sidebar-link <?= isActive('/dossiers', $currentPath) ?>">
            <i class="bi bi-folder2-open"></i><span>Dossiers</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('ordonnances')): ?>
        <a href="<?= BASE_URL ?>/ordonnances" class="sidebar-link <?= isActive('/ordonnances', $currentPath) ?>">
            <i class="bi bi-file-earmark-text"></i><span>Ordonnances JI</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('controles_judiciaires')): ?>
        <a href="<?= BASE_URL ?>/controles-judiciaires" class="sidebar-link <?= isActive('/controles-judiciaires', $currentPath) ?>">
            <i class="bi bi-shield-check"></i><span>Contrôles judiciaires</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('expertises')): ?>
        <a href="<?= BASE_URL ?>/expertises" class="sidebar-link <?= isActive('/expertises', $currentPath) ?>">
            <i class="bi bi-microscope"></i><span>Expertises</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('commissions_rogatoires')): ?>
        <a href="<?= BASE_URL ?>/commissions-rogatoires" class="sidebar-link <?= isActive('/commissions-rogatoires', $currentPath) ?>">
            <i class="bi bi-send"></i><span>Commissions rogatoires</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('scelles')): ?>
        <a href="<?= BASE_URL ?>/scelles" class="sidebar-link <?= isActive('/scelles', $currentPath) ?>">
            <i class="bi bi-archive"></i><span>Scellés</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php
        $showJugement = $canMenu('audiences') || $canMenu('jugements') || $canMenu('voies_recours');
        if ($showJugement):
        ?>
        <!-- ══════════ JUGEMENT & AUDIENCE ══════════ -->
        <div class="sidebar-section">Jugement &amp; Audience</div>
        <?php if ($canMenu('audiences')): ?>
        <a href="<?= BASE_URL ?>/audiences" class="sidebar-link <?= isActive('/audiences', $currentPath) ?>">
            <i class="bi bi-calendar-week"></i><span>Audiences</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('jugements')): ?>
        <a href="<?= BASE_URL ?>/jugements" class="sidebar-link <?= isActive('/jugements', $currentPath) ?>">
            <i class="bi bi-hammer"></i><span>Jugements</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('voies_recours')): ?>
        <a href="<?= BASE_URL ?>/voies-recours" class="sidebar-link <?= isActive('/voies-recours', $currentPath) ?>">
            <i class="bi bi-arrow-repeat"></i><span>Voies de recours</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php
        $showDetention = $canMenu('detenus') || $canMenu('mandats');
        if ($showDetention):
        ?>
        <!-- ══════════ DÉTENTION & MANDATS ══════════ -->
        <div class="sidebar-section">Détention &amp; Mandats</div>
        <?php if ($canMenu('detenus')): ?>
        <a href="<?= BASE_URL ?>/detenus" class="sidebar-link <?= isActive('/detenus', $currentPath) ?>">
            <i class="bi bi-person-lock"></i><span>Population Carcérale</span>
        </a>
        <?php endif; ?>
        <?php if ($canMenu('mandats')): ?>
        <a href="<?= BASE_URL ?>/mandats" class="sidebar-link <?= isActive('/mandats', $currentPath) ?>">
            <i class="bi bi-file-ruled" style="color:#ef4444"></i><span>Mandats de Justice</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($canMenu('carte')): ?>
        <!-- ══════════ SÉCURITÉ ══════════ -->
        <div class="sidebar-section">Sécurité</div>
        <a href="<?= BASE_URL ?>/carte" class="sidebar-link <?= isActive('/carte', $currentPath) ?>">
            <i class="bi bi-map"></i><span>Carte Antiterroriste</span>
        </a>
        <?php endif; ?>

        <!-- ══════════ CONFIGURATION SYSTÈME ══════════ -->
        <div class="sidebar-section">Configuration Système</div>
        <?php if ($canMenu('alertes')): ?>
        <a href="<?= BASE_URL ?>/alertes" class="sidebar-link <?= isActive('/alertes', $currentPath) ?> d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bell"></i> Alertes</span>
            <?php if ($nbAlertes > 0): ?>
            <span class="badge bg-danger rounded-pill"><?= $nbAlertes ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasRole(['admin','president']) && $canMenu('users')): ?>
        <a href="<?= BASE_URL ?>/users" class="sidebar-link <?= isActive('/users', $currentPath) ?>">
            <i class="bi bi-people"></i><span>Utilisateurs</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasRole(['admin'])): ?>
        <a href="<?= BASE_URL ?>/admin/droits" class="sidebar-link <?= isActive('/admin/droits', $currentPath) ?>">
            <i class="bi bi-shield-lock"></i><span>Droits &amp; Accès</span>
        </a>
        <?php endif; ?>
        <?php if ((Auth::hasRole(['admin','procureur'])) && $canMenu('config')): ?>
        <a href="<?= BASE_URL ?>/config" class="sidebar-link <?= isActive('/config', $currentPath) ?>">
            <i class="bi bi-gear-fill"></i><span>Configuration</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Version en bas de sidebar -->
    <div style="padding:12px 18px;border-top:1px solid rgba(255,255,255,.08);margin-top:auto;">
        <small style="color:rgba(255,255,255,.3);font-size:.65rem;">
            v<?= APP_VERSION ?> · <?= date('Y') ?>
        </small>
    </div>
</div>

<!-- ═════════════════════════════ CONTENU PRINCIPAL ═════════════════════════════ -->
<div class="main-content" id="mainContent">

    <!-- ── Top Navbar ── -->
    <nav class="top-navbar">
        <button class="btn btn-sm btn-outline-secondary me-2" id="sidebarToggle" aria-label="Menu">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="navbar-brand-text d-none d-lg-block text-truncate" style="max-width:300px;">
            <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : APP_FULL_NAME ?>
        </span>
        <!-- Mobile: nom court -->
        <span class="navbar-brand-text d-lg-none fw-bold" style="font-size:.85rem">TGI-NY</span>

        <div class="ms-auto d-flex align-items-center gap-2">
            <!-- Alertes -->
            <a href="<?= BASE_URL ?>/alertes" class="btn btn-sm btn-outline-warning position-relative" title="Alertes">
                <i class="bi bi-bell"></i>
                <?php if ($nbAlertes > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
                    <?= $nbAlertes ?>
                </span>
                <?php endif; ?>
            </a>

            <!-- Profil -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle user-btn d-flex align-items-center gap-1"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-sm-inline text-truncate" style="max-width:120px;">
                        <?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:200px;">
                    <li>
                        <div class="px-3 py-2">
                            <div class="fw-semibold small"><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($currentUser['role_lib'] ?? '') ?></div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ── Flash messages ── -->
    <?php $flash = $flash ?? []; ?>
    <?php $hasFlash = false;
    foreach (['success','error','warning','info'] as $ft) { if (!empty($flash[$ft])) { $hasFlash = true; break; } }
    if ($hasFlash): ?>
    <div class="px-3 px-md-4 pt-3">
        <?php foreach (['success','error','warning','info'] as $ftype): ?>
        <?php if (!empty($flash[$ftype])): foreach ($flash[$ftype] as $msg): ?>
        <div class="alert alert-<?= $ftype === 'error' ? 'danger' : $ftype ?> alert-dismissible fade show py-2" role="alert">
            <i class="bi bi-<?= $ftype === 'success' ? 'check-circle-fill' : ($ftype === 'error' ? 'exclamation-triangle-fill' : ($ftype === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill')) ?> me-2"></i>
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php endforeach; endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Page content ── -->
    <div class="page-content px-3 px-md-4 pb-4">
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
