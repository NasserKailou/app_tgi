<?php $pageTitle = 'Mandat ' . htmlspecialchars($mandat['numero']); ?>

<?php
// ─── Libellés ────────────────────────────────────────────────────────
$typeLabels = [
    'arret'        => ['danger',   "Mandat d'arrêt"],
    'depot'        => ['dark',     'Mandat de dépôt'],
    'amener'       => ['warning',  "Mandat d'amener"],
    'comparution'  => ['info',     'Mandat de comparution'],
    'perquisition' => ['secondary','Mandat de perquisition'],
    'liberation'   => ['success',  'Mandat de libération'],
];
$statutLabels = [
    'emis'      => ['primary',   'Émis'],
    'signifie'  => ['info',      'Signifié'],
    'execute'   => ['success',   'Exécuté'],
    'annule'    => ['danger',    'Annulé'],
    'expire'    => ['secondary', 'Expiré'],
];
[$tc, $tl] = $typeLabels[$mandat['type_mandat']] ?? ['secondary', $mandat['type_mandat']];
[$sc, $sl] = $statutLabels[$mandat['statut']]    ?? ['secondary', $mandat['statut']];

// ─── Cible ───────────────────────────────────────────────────────────
if (!empty($mandat['detenu_label'])) {
    $cible = $mandat['detenu_label'] . ' (Détenu' . (!empty($mandat['numero_ecrou']) ? ' — N° écrou ' . $mandat['numero_ecrou'] : '') . ')';
} elseif (!empty($mandat['partie_label'])) {
    $cible = $mandat['partie_label'] . ' (Partie' . (!empty($mandat['type_partie']) ? ' — ' . $mandat['type_partie'] : '') . ')';
} elseif (!empty($mandat['nouveau_nom'])) {
    $cible = trim(($mandat['nouveau_prenom'] ?? '') . ' ' . $mandat['nouveau_nom']);
} else {
    $cible = 'Non précisé';
}

// ─── Droits utilisateur ──────────────────────────────────────────────
$currentUser = Auth::currentUser();
$myId        = (int)($currentUser['id'] ?? 0);
$roleCode    = $currentUser['role_code'] ?? '';
$isAdmin     = in_array($roleCode, ['admin', 'procureur'], true);
$isOwner     = (int)($mandat['emetteur_id'] ?? 0) === $myId;

$today    = date('Y-m-d');
$expired  = !empty($mandat['date_expiration']) && $mandat['date_expiration'] < $today;
$blocking = in_array($mandat['statut'] ?? '', ['execute', 'annule', 'expire'], true);

$canEdit   = !$blocking && ($isAdmin || $isOwner);
$canDelete = $isAdmin && ($mandat['statut'] ?? '') !== 'execute';
$canStatut = Auth::hasRole(['admin', 'procureur', 'greffier', 'juge_instruction']);
?>

<!-- ═══════════════ Barre d'en-tête ═══════════════ -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div class="d-flex align-items-center flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/mandats" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0 fw-bold">
            <i class="bi bi-file-ruled text-danger me-2"></i><?= htmlspecialchars($mandat['numero']) ?>
        </h1>
        <span class="badge bg-<?= $tc ?> fs-6"><?= $tl ?></span>
        <span class="badge bg-<?= $sc ?>"><?= $sl ?></span>
        <?php if ($expired): ?>
            <span class="badge bg-danger"><i class="bi bi-clock-history me-1"></i>EXPIRÉ</span>
        <?php endif; ?>
        <?php if (!empty($mandat['flagrant_delit'])): ?>
            <span class="badge bg-warning text-dark">
                <i class="bi bi-exclamation-octagon-fill me-1"></i>Flagrant délit
            </span>
        <?php endif; ?>
    </div>

    <!-- Boutons d'action -->
    <div class="btn-group" role="group">
        <a href="<?= BASE_URL ?>/mandats/print/<?= $mandat['id'] ?>" target="_blank"
           class="btn btn-outline-dark">
            <i class="bi bi-printer me-1"></i>Imprimer
        </a>

        <?php if ($canEdit): ?>
            <a href="<?= BASE_URL ?>/mandats/edit/<?= $mandat['id'] ?>"
               class="btn btn-outline-warning">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
        <?php endif; ?>

        <?php if ($canDelete): ?>
            <form method="post" action="<?= BASE_URL ?>/mandats/delete/<?= $mandat['id'] ?>"
                  class="d-inline"
                  onsubmit="return confirm('⚠ Supprimer définitivement le mandat <?= htmlspecialchars(addslashes($mandat['numero'])) ?> ?\nCette action est irréversible.');">
                <?= CSRF::field() ?>
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Supprimer
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════ Messages flash ═══════════════ -->
<?php if (!empty($flash['success'])): foreach ((array)$flash['success'] as $msg): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endforeach; endif; ?>
<?php if (!empty($flash['error'])): foreach ((array)$flash['error'] as $msg): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endforeach; endif; ?>

<?php if ($blocking): ?>
    <div class="alert alert-secondary py-2 small">
        <i class="bi bi-lock-fill me-1"></i>
        Ce mandat est <strong><?= strtolower($sl) ?></strong>. Il ne peut plus être modifié
        <?= $isAdmin ? '' : 'ni supprimé' ?>.
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- ═══════════════ Colonne gauche : détails ═══════════════ -->
    <div class="col-lg-8">

        <!-- Détails du mandat -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white fw-semibold">
                <i class="bi bi-info-circle me-2"></i>Détails du mandat
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="w-35 text-muted">Numéro</th>
                        <td class="fw-bold"><?= htmlspecialchars($mandat['numero']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Type</th>
                        <td><span class="badge bg-<?= $tc ?>"><?= $tl ?></span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Cible</th>
                        <td class="fw-semibold">
                            <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($cible) ?>
                        </td>
                    </tr>
                    <?php if (!empty($mandat['numero_rg'])): ?>
                        <tr>
                            <th class="text-muted">Dossier</th>
                            <td>
                                <a href="<?= BASE_URL ?>/dossiers/show/<?= (int)$mandat['dossier_id'] ?>">
                                    <?= htmlspecialchars($mandat['numero_rg']) ?>
                                </a>
                                <?= !empty($mandat['dossier_objet']) ? ' — ' . htmlspecialchars($mandat['dossier_objet']) : '' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th class="text-muted">Infraction(s)</th>
                        <td><?= htmlspecialchars($mandat['infraction_libelle'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Lieu d'exécution</th>
                        <td><?= htmlspecialchars($mandat['lieu_execution'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Date d'émission</th>
                        <td>
                            <?= !empty($mandat['date_emission']) ? date('d/m/Y', strtotime($mandat['date_emission'])) : '—' ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Date d'expiration</th>
                        <td class="<?= $expired ? 'text-danger fw-bold' : '' ?>">
                            <?= !empty($mandat['date_expiration']) ? date('d/m/Y', strtotime($mandat['date_expiration'])) : 'Illimité' ?>
                        </td>
                    </tr>
                    <?php if (!empty($mandat['date_exhibe'])): ?>
                        <tr>
                            <th class="text-muted">Exhibé au prévenu le</th>
                            <td><?= date('d/m/Y', strtotime($mandat['date_exhibe'])) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th class="text-muted">Émetteur</th>
                        <td>
                            <?= htmlspecialchars($mandat['emetteur_nom']) ?>
                            <small class="text-muted">(<?= htmlspecialchars($mandat['emetteur_role'] ?? '') ?>)</small>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Statut</th>
                        <td><span class="badge bg-<?= $sc ?>"><?= $sl ?></span></td>
                    </tr>
                    <?php if (!empty($mandat['date_execution'])): ?>
                        <tr>
                            <th class="text-muted">Date d'exécution</th>
                            <td><?= date('d/m/Y', strtotime($mandat['date_execution'])) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($mandat['executant_nom'])): ?>
                        <tr>
                            <th class="text-muted">Exécuté par</th>
                            <td><?= htmlspecialchars($mandat['executant_nom']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($mandat['created_at'])): ?>
                        <tr>
                            <th class="text-muted">Créé le</th>
                            <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($mandat['created_at'])) ?></small></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($mandat['updated_at'])): ?>
                        <tr>
                            <th class="text-muted">Dernière modification</th>
                            <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($mandat['updated_at'])) ?></small></td>
                        </tr>
                    <?php endif; ?>
                </table>

                <hr>
                <h6 class="fw-bold mb-2"><i class="bi bi-card-text me-1"></i>Motif :</h6>
                <p class="mb-0"><?= nl2br(htmlspecialchars($mandat['motif'])) ?></p>

                <?php if (!empty($mandat['observations'])): ?>
                    <hr>
                    <h6 class="fw-bold mb-2"><i class="bi bi-chat-left-text me-1"></i>Observations :</h6>
                    <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($mandat['observations'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Identité de la personne ciblée (si nouvelle) -->
        <?php if (!empty($mandat['nouveau_nom']) && empty($mandat['detenu_id']) && empty($mandat['partie_id'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white fw-semibold">
                    <i class="bi bi-person-lines-fill me-2"></i>Identité de la personne ciblée
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nom complet</small>
                            <div class="fw-bold">
                                <?= htmlspecialchars(trim(($mandat['nouveau_prenom'] ?? '') . ' ' . $mandat['nouveau_nom'])) ?>
                            </div>
                        </div>

                        <?php if (!empty($mandat['sexe'])): ?>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Sexe</small>
                                <div><?= $mandat['sexe'] === 'F' ? 'Féminin' : 'Masculin' ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['nouveau_nationalite'])): ?>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Nationalité</small>
                                <div><?= htmlspecialchars($mandat['nouveau_nationalite']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['nouveau_ddn'])): ?>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Date de naissance</small>
                                <div><?= date('d/m/Y', strtotime($mandat['nouveau_ddn'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['nouveau_lieu_naissance'])): ?>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Lieu de naissance</small>
                                <div><?= htmlspecialchars($mandat['nouveau_lieu_naissance']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['nouveau_profession'])): ?>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Profession</small>
                                <div><?= htmlspecialchars($mandat['nouveau_profession']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['nouveau_pere'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Nom du père</small>
                                <div><?= htmlspecialchars($mandat['nouveau_pere']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['nouveau_mere'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Nom de la mère</small>
                                <div><?= htmlspecialchars($mandat['nouveau_mere']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['nouveau_adresse'])): ?>
                            <div class="col-12">
                                <small class="text-muted d-block">Adresse / Domicile</small>
                                <div><?= htmlspecialchars($mandat['nouveau_adresse']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['situation_famille'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Situation de famille</small>
                                <div><?= htmlspecialchars($mandat['situation_famille']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['service_militaire'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Service militaire</small>
                                <div><?= htmlspecialchars($mandat['service_militaire']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mandat['condamnations'])): ?>
                            <div class="col-12">
                                <small class="text-muted d-block">Condamnations antérieures</small>
                                <div><?= htmlspecialchars($mandat['condamnations']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- ═══════════════ Colonne droite : statut ═══════════════ -->
    <?php if ($canStatut): ?>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-lg-top" style="top:80px">
                <div class="card-header fw-semibold bg-secondary text-white">
                    <i class="bi bi-arrow-repeat me-2"></i>Mettre à jour le statut
                </div>
                <div class="card-body">
                    <?php if ($blocking): ?>
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Le statut actuel est définitif. Modifier l'état nécessite des privilèges administrateur.
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/mandats/update-statut/<?= $mandat['id'] ?>">
                        <?= CSRF::field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nouveau statut</label>
                            <select name="statut" class="form-select" required>
                                <?php foreach ([
                                    'emis'     => 'Émis',
                                    'signifie' => 'Signifié',
                                    'execute'  => 'Exécuté',
                                    'annule'   => 'Annulé',
                                    'expire'   => 'Expiré',
                                ] as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $mandat['statut'] === $k ? 'selected' : '' ?>>
                                        <?= $v ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date d'exécution</label>
                            <input type="date" name="date_execution" class="form-control"
                                   value="<?= htmlspecialchars($mandat['date_execution'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Exécuté par (OPJ / unité)</label>
                            <input type="text" name="executant_nom" class="form-control"
                                   placeholder="Nom OPJ ou unité"
                                   value="<?= htmlspecialchars($mandat['executant_nom'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Observations</label>
                            <textarea name="observations" class="form-control" rows="3"
                                      placeholder="Observations…"><?= htmlspecialchars($mandat['observations'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-1"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>

            <!-- Carte d'aide rapide -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header fw-semibold bg-light">
                    <i class="bi bi-lightbulb text-warning me-2"></i>Aide
                </div>
                <div class="card-body small">
                    <p class="mb-2"><strong>Émis</strong> — Le mandat vient d'être créé.</p>
                    <p class="mb-2"><strong>Signifié</strong> — Notifié à la personne concernée.</p>
                    <p class="mb-2"><strong>Exécuté</strong> — Action réalisée (arrestation, dépôt…).</p>
                    <p class="mb-2"><strong>Annulé</strong> — Révoqué avant exécution.</p>
                    <p class="mb-0"><strong>Expiré</strong> — Date de validité dépassée.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
