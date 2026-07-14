<?php $pageTitle = 'Situation des PVs'; ?>
<?php
$statLabels = [
    'nouveau'                   => ['Nouveau',              'bg-secondary'],
    'recu'                      => ['Reçu',                 'bg-info text-dark'],
    'en_traitement'             => ['En traitement',        'bg-warning text-dark'],
    'classe'                    => ['Classé',               'bg-dark'],
    'transfere'                 => ['Transféré',            'bg-primary'],
    'transfere_instruction'     => ['Instruction',          'bg-success'],
    'transfere_jugement_direct' => ['Audience directe',     'bg-purple'],
];
$mpLabels = [
    'RI'    => ['RI — Réquisitoire Introductif', 'primary'],
    'CD'    => ['CD — Citation Directe',          'info'],
    'FD'    => ['FD — Flagrant Délit',            'danger'],
    'CRPC'  => ['CRPC',                           'purple'],
    'autre' => ['Autre',                          'secondary'],
];
$typeLabels = [
    'droit_commun_mineur'    => 'Droit Commun — Mineur',
    'droit_commun_majeur'    => 'Droit Commun — Majeur',
    'pole_antiterro_mineur'  => 'Pôle Antiterroriste — Mineur',
    'pole_antiterro_majeur'  => 'Pôle Antiterroriste — Majeur',
    'pole_economique'        => 'Pôle Économique',
    'penale'                 => 'Pénale',
    'civile'                 => 'Civile',
];
?>
<style>
.bg-purple  { background:#6f42c1 !important; }
.text-purple{ color:#6f42c1 !important; }
.border-purple{ border-color:#6f42c1 !important; }
.stat-card { border-radius:12px; transition:transform .15s; }
.stat-card:hover { transform:translateY(-3px); box-shadow:0 6px 20px rgba(0,0,0,.12); }
.table-situation th { position:sticky; top:0; background:#1a3c5e; color:#fff; z-index:2; font-size:.8rem; white-space:nowrap; }
.table-situation td { font-size:.82rem; vertical-align:middle; }
.filter-card { border-left:4px solid #1a3c5e; }
.badge-mode-RI     { background:#0d6efd; color:#fff; }
.badge-mode-CD     { background:#0dcaf0; color:#000; }
.badge-mode-FD     { background:#dc3545; color:#fff; }
.badge-mode-CRPC   { background:#6f42c1; color:#fff; }
.badge-mode-autre  { background:#6c757d; color:#fff; }
.badge-mode-null   { background:#adb5bd; color:#000; }
</style>

<div class="mb-4 mt-2 d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Situation des PVs</h4>
        <p class="text-muted mb-0 small">Tableau de bord périodique — filtrez par date, statut, substitut, unité…</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/situation/crpc?<?= http_build_query(array_filter([
            'date_debut'   => $dateDebut,
            'date_fin'     => $dateFin,
            'substitut_id' => $substitutId ?: '',
        ])) ?>" class="btn btn-sm fw-semibold text-white" style="background:#6f42c1;border-color:#6f42c1;">
            <i class="bi bi-file-earmark-text me-1"></i>Situation CRPC
        </a>
        <a href="<?= BASE_URL ?>/situation/pv/export?<?= http_build_query(array_filter([
            'date_debut'     => $dateDebut,
            'date_fin'       => $dateFin,
            'statut'         => $statut,
            'mode_poursuite' => $modeP,
            'substitut_id'   => $substitutId ?: '',
            'unite_id'       => $uniteId ?: '',
            'type_affaire'   => $typeAff,
        ])) ?>" class="btn btn-outline-success btn-sm">
            <i class="bi bi-download me-1"></i>Exporter CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i>Imprimer
        </button>
    </div>
</div>

<!-- ══ Formulaire de filtres ══ -->
<div class="card border-0 shadow-sm mb-4 filter-card">
    <div class="card-header bg-white fw-semibold small"><i class="bi bi-funnel me-2 text-primary"></i>Filtres de la période</div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/situation/pv" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Du</label>
                <input type="date" name="date_debut" class="form-control form-control-sm" value="<?= $dateDebut ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Au</label>
                <input type="date" name="date_fin" class="form-control form-control-sm" value="<?= $dateFin ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($statLabels as $k => [$lbl, $cls]): ?>
                    <option value="<?= $k ?>" <?= $statut === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Mode de poursuite</label>
                <select name="mode_poursuite" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <?php foreach ($mpLabels as $k => [$lbl, $c]): ?>
                    <option value="<?= $k ?>" <?= $modeP === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Substitut</label>
                <select name="substitut_id" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <?php foreach ($substituts as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $substitutId === (int)$s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Unité d'enquête</label>
                <select name="unite_id" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($unites as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $uniteId === (int)$u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Type d'affaire</label>
                <select name="type_affaire" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <?php foreach ($typeLabels as $k => $l): ?>
                    <option value="<?= $k ?>" <?= $typeAff === $k ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="<?= BASE_URL ?>/situation/pv" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ══ Cartes de synthèse ══ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm text-center py-3 px-2" style="border-top:4px solid #1a3c5e !important;">
            <div class="fs-1 fw-bold text-primary"><?= $total ?></div>
            <div class="small text-muted fw-semibold">PVs total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm text-center py-3 px-2" style="border-top:4px solid #ffc107 !important;">
            <div class="fs-1 fw-bold text-warning"><?= $byStatut['en_traitement'] ?? 0 ?></div>
            <div class="small text-muted fw-semibold">En traitement</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm text-center py-3 px-2" style="border-top:4px solid #198754 !important;">
            <div class="fs-1 fw-bold text-success"><?= ($byStatut['transfere_instruction'] ?? 0) + ($byStatut['transfere_jugement_direct'] ?? 0) + ($byStatut['transfere'] ?? 0) ?></div>
            <div class="small text-muted fw-semibold">Transférés</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm text-center py-3 px-2" style="border-top:4px solid #dc3545 !important;">
            <div class="fs-1 fw-bold text-danger"><?= $byStatut['classe'] ?? 0 ?></div>
            <div class="small text-muted fw-semibold">Classés sans suite</div>
        </div>
    </div>
    <?php if ($delaiMoyenAffectation !== null): ?>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm text-center py-3 px-2" style="border-top:4px solid #0dcaf0 !important;">
            <div class="fs-1 fw-bold text-info"><?= $delaiMoyenAffectation ?></div>
            <div class="small text-muted fw-semibold">Jours moy. avant affectation</div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm text-center py-3 px-2" style="border-top:4px solid #6f42c1 !important;">
            <div class="fs-1 fw-bold text-purple"><?= $byMode['CRPC'] ?? 0 ?></div>
            <div class="small text-muted fw-semibold">Dossiers CRPC</div>
        </div>
    </div>
</div>

<!-- ══ Mini-graphes de répartition (2 colonnes) ══ -->
<div class="row g-3 mb-4">
    <!-- Par statut -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-pie-chart me-1"></i>Répartition par statut</div>
            <div class="card-body p-3">
                <?php foreach ($byStatut as $k => $n): ?>
                <?php
                $pct = $total > 0 ? round($n/$total*100) : 0;
                [$lbl,$cls] = $statLabels[$k] ?? [$k,'bg-secondary'];
                ?>
                <div class="d-flex align-items-center mb-2 gap-2">
                    <span class="badge <?= $cls ?> text-nowrap" style="min-width:130px;font-size:.72rem;"><?= $lbl ?></span>
                    <div class="flex-grow-1">
                        <div class="progress" style="height:14px;border-radius:8px;">
                            <div class="progress-bar <?= $cls ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                    <span class="fw-bold small text-end" style="min-width:38px;"><?= $n ?></span>
                    <span class="text-muted small" style="min-width:36px;"><?= $pct ?>%</span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($byStatut)): ?><p class="text-muted small text-center">Aucune donnée</p><?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Par mode de poursuite -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-bar-chart me-1"></i>Répartition par mode de poursuite</div>
            <div class="card-body p-3">
                <?php foreach ($byMode as $k => $n): ?>
                <?php
                $pct = $total > 0 ? round($n/$total*100) : 0;
                [$lbl,$c] = $mpLabels[$k] ?? [$k,'secondary'];
                ?>
                <div class="d-flex align-items-center mb-2 gap-2">
                    <span class="badge badge-mode-<?= $k ?> text-nowrap" style="min-width:130px;font-size:.72rem;"><?= $lbl ?></span>
                    <div class="flex-grow-1">
                        <div class="progress" style="height:14px;border-radius:8px;">
                            <div class="progress-bar bg-<?= $c ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                    <span class="fw-bold small text-end" style="min-width:38px;"><?= $n ?></span>
                    <span class="text-muted small" style="min-width:36px;"><?= $pct ?>%</span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($byMode)): ?><p class="text-muted small text-center">Aucun mode défini</p><?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Par unité d'enquête -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-shield me-1"></i>Répartition par unité d'enquête</div>
            <div class="card-body p-3">
                <?php arsort($byUnite); foreach (array_slice($byUnite, 0, 10) as $u => $n): ?>
                <?php $pct = $total > 0 ? round($n/$total*100) : 0; ?>
                <div class="d-flex align-items-center mb-2 gap-2">
                    <span class="small text-truncate" style="min-width:110px;max-width:130px;" title="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></span>
                    <div class="flex-grow-1">
                        <div class="progress" style="height:14px;border-radius:8px;">
                            <div class="progress-bar" style="width:<?= $pct ?>%;background:#1a3c5e;"></div>
                        </div>
                    </div>
                    <span class="fw-bold small text-end" style="min-width:38px;"><?= $n ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($byUnite)): ?><p class="text-muted small text-center">Aucune unité</p><?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Par substitut -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-person-badge me-1"></i>Charge par substitut</div>
            <div class="card-body p-3">
                <?php arsort($bySub); foreach ($bySub as $s => $n): ?>
                <?php $pct = $total > 0 ? round($n/$total*100) : 0; ?>
                <div class="d-flex align-items-center mb-2 gap-2">
                    <span class="small text-truncate" style="min-width:120px;max-width:140px;"><?= htmlspecialchars($s) ?></span>
                    <div class="flex-grow-1">
                        <div class="progress" style="height:14px;border-radius:8px;">
                            <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                    <span class="fw-bold small text-end" style="min-width:38px;"><?= $n ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($bySub)): ?><p class="text-muted small text-center">Aucun substitut</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ Tableau détaillé ══ -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between fw-semibold">
        <span><i class="bi bi-table me-2 text-primary"></i>Détail des <?= $total ?> PV(s) — Période : <?= date('d/m/Y', strtotime($dateDebut)) ?> au <?= date('d/m/Y', strtotime($dateFin)) ?></span>
        <span class="badge bg-primary"><?= $total ?></span>
    </div>
    <?php if (empty($pvList)): ?>
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
        Aucun PV ne correspond aux critères sélectionnés.
    </div>
    <?php else: ?>
    <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
        <table class="table table-hover table-sm table-bordered mb-0 table-situation">
            <thead>
                <tr>
                    <th>#</th>
                    <th>N° RG</th>
                    <th>N° PV</th>
                    <th>Date PV</th>
                    <th>Réception</th>
                    <th>Type</th>
                    <th>Unité</th>
                    <th>Infraction</th>
                    <th>Substitut</th>
                    <th>Affectation</th>
                    <th>Mode</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pvList as $i => $pv): ?>
            <?php
            [$sLbl,$sCls] = $statLabels[$pv['statut']] ?? [$pv['statut'],'bg-secondary'];
            $mp = $pv['mode_poursuite'] ?? '';
            [$mpLbl,$mpC] = $mpLabels[$mp] ?? [$mp ?: '—','secondary'];
            ?>
            <tr>
                <td class="text-muted"><?= $i+1 ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="fw-semibold text-decoration-none" style="color:#1a3c5e;">
                        <?= htmlspecialchars($pv['numero_rg']) ?>
                    </a>
                </td>
                <td class="font-monospace small"><?= htmlspecialchars($pv['numero_pv'] ?? '—') ?></td>
                <td><?= $pv['date_pv'] ? date('d/m/Y', strtotime($pv['date_pv'])) : '—' ?></td>
                <td><?= $pv['date_reception'] ? date('d/m/Y', strtotime($pv['date_reception'])) : '—' ?></td>
                <td>
                    <span class="badge bg-light text-dark border small" style="font-size:.68rem;">
                        <?= $typeLabels[$pv['type_affaire']] ?? $pv['type_affaire'] ?>
                    </span>
                </td>
                <td class="small"><?= htmlspecialchars($pv['unite_nom'] ?? '—') ?></td>
                <td class="small text-truncate" style="max-width:130px;" title="<?= htmlspecialchars($pv['infraction_libelle'] ?? '') ?>">
                    <?php if (!empty($pv['infraction_code'])): ?>
                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($pv['infraction_code']) ?></span>
                    <?php endif; ?>
                    <?= htmlspecialchars($pv['infraction_libelle'] ?? '—') ?>
                </td>
                <td class="small">
                    <?php if (!empty($pv['sub_nom'])): ?>
                    <span class="text-success fw-semibold"><?= htmlspecialchars($pv['sub_prenom'].' '.$pv['sub_nom']) ?></span>
                    <?php else: ?>
                    <span class="text-muted fst-italic">Non affecté</span>
                    <?php endif; ?>
                </td>
                <td class="small"><?= !empty($pv['date_affectation_substitut']) ? date('d/m/Y', strtotime($pv['date_affectation_substitut'])) : '—' ?></td>
                <td>
                    <span class="badge badge-mode-<?= $mp ?: 'null' ?>" style="font-size:.68rem;"><?= $mpLbl ?></span>
                </td>
                <td>
                    <span class="badge <?= $sCls ?>" style="font-size:.68rem;"><?= $sLbl ?></span>
                </td>
                <td>
                    <a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="btn btn-xs btn-outline-primary btn-sm" title="Voir">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
