<?php $pageTitle = 'Situation CRPC — ' . date('d/m/Y', strtotime($dateDebut)) . ' au ' . date('d/m/Y', strtotime($dateFin)); ?>
<style>
/* ── Styles écran ── */
.crpc-page-header { background:#6f42c1; color:#fff; border-radius:8px 8px 0 0; }
.crpc-section-title { background:#1a3c5e; color:#fff; font-size:.78rem; font-weight:700;
    letter-spacing:.04em; padding:6px 12px; border-radius:4px; margin-bottom:8px; }
.crpc-card { border:1px solid #dee2e6; border-radius:8px; margin-bottom:24px; overflow:hidden;
    page-break-inside:avoid; break-inside:avoid; }
.crpc-card-header { background:#6f42c1; color:#fff; padding:8px 14px; font-weight:700; font-size:.85rem; }
.crpc-badge-homo  { background:#198754; color:#fff; }
.crpc-badge-refuse { background:#dc3545; color:#fff; }
.crpc-badge-attente { background:#6c757d; color:#fff; }
.crpc-table { width:100%; font-size:.78rem; border-collapse:collapse; }
.crpc-table th { background:#f0edf8; color:#4a1fb8; font-weight:700; padding:5px 8px;
    border:1px solid #c9b8f0; white-space:nowrap; }
.crpc-table td { padding:5px 8px; border:1px solid #dee2e6; vertical-align:top; }
.crpc-table tr:nth-child(even) td { background:#faf9ff; }
.crpc-stat-box { border:2px solid #6f42c1; border-radius:8px; padding:10px 16px; text-align:center; }
.crpc-stat-box .num { font-size:2rem; font-weight:800; color:#6f42c1; }
.crpc-stat-box .lbl { font-size:.72rem; color:#6c757d; font-weight:600; }
.filter-pill { background:#e8e0f8; color:#4a1fb8; border-radius:20px;
    padding:2px 10px; font-size:.72rem; font-weight:600; }

/* ── Styles impression ── */
@media print {
    body { font-size: 9pt !important; }
    .no-print, .navbar, .sidebar, nav, .breadcrumb,
    .btn, form.filter-form, .filter-card, footer { display: none !important; }
    .container, .container-fluid { max-width:100% !important; padding:0 !important; }
    .crpc-card { border:1px solid #999 !important; page-break-inside:avoid; }
    .crpc-card-header { background:#6f42c1 !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .crpc-section-title { background:#1a3c5e !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .crpc-table th { background:#e8e0f8 !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .page-break { page-break-before:always; }
    .page-break-inside-avoid { page-break-inside:avoid; }
    @page { margin:1.2cm 1.5cm; size: A4 portrait; }
    .print-page-header { display:block !important; }
    h4.fw-bold { font-size:12pt !important; }
    .crpc-stat-box .num { font-size:16pt !important; }
}
.print-page-header { display:none; }
</style>

<!-- En-tête écran -->
<div class="mb-3 mt-2">
    <nav aria-label="breadcrumb" class="no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/situation/pv">Situation PVs</a></li>
            <li class="breadcrumb-item active">Situation CRPC</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:#6f42c1;">
                <i class="bi bi-file-earmark-text me-2"></i>Situation des dossiers CRPC
            </h4>
            <p class="text-muted mb-0 small">
                TGI Hors-Classe de Niamey —
                Période du <strong><?= date('d/m/Y', strtotime($dateDebut)) ?></strong>
                au <strong><?= date('d/m/Y', strtotime($dateFin)) ?></strong>
                <?php if ($substitutNom): ?>
                — Substitut : <span class="filter-pill"><?= htmlspecialchars($substitutNom) ?></span>
                <?php endif; ?>
                <?php if ($statutFilter): ?>
                — Statut : <span class="filter-pill"><?= htmlspecialchars($statutFilter) ?></span>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap no-print">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i>Imprimer / PDF
            </button>
            <a href="<?= BASE_URL ?>/situation/crpc?<?= http_build_query(array_filter([
                'date_debut'   => $dateDebut,
                'date_fin'     => $dateFin,
                'substitut_id' => $substitutId ?: '',
                'statut'       => $statutFilter,
                'export'       => 'csv',
            ])) ?>" class="btn btn-outline-success btn-sm">
                <i class="bi bi-download me-1"></i>Exporter CSV
            </a>
            <a href="<?= BASE_URL ?>/situation/pv" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour situation PVs
            </a>
        </div>
    </div>
</div>

<!-- ══ Formulaire de filtres ══ -->
<div class="card border-0 shadow-sm mb-4 no-print" style="border-left:4px solid #6f42c1;">
    <div class="card-header bg-white fw-semibold small">
        <i class="bi bi-funnel me-2 text-purple" style="color:#6f42c1;"></i>Filtres
    </div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/situation/crpc" class="row g-2 align-items-end filter-form">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Du</label>
                <input type="date" name="date_debut" class="form-control form-control-sm" value="<?= $dateDebut ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Au</label>
                <input type="date" name="date_fin" class="form-control form-control-sm" value="<?= $dateFin ?>">
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
                <label class="form-label small fw-semibold">Statut CRPC</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="en_cours"    <?= $statutFilter==='en_cours'    ?'selected':'' ?>>En cours</option>
                    <option value="homologuee"  <?= $statutFilter==='homologuee'  ?'selected':'' ?>>Homologuée</option>
                    <option value="refusee"     <?= $statutFilter==='refusee'     ?'selected':'' ?>>Refusée</option>
                    <option value="abandonnee"  <?= $statutFilter==='abandonnee'  ?'selected':'' ?>>Abandonnée</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="<?= BASE_URL ?>/situation/crpc" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ══ En-tête imprimé (visible uniquement à l'impression) ══ -->
<div class="print-page-header mb-3">
    <table width="100%" style="border-bottom:2px solid #6f42c1;padding-bottom:6pt;margin-bottom:8pt;">
        <tr>
            <td style="width:20%;text-align:center;vertical-align:middle;">
                <div style="font-size:8pt;color:#333;font-weight:bold;">REPUBLIQUE DU NIGER</div>
                <div style="font-size:7pt;color:#555;">Fraternité — Travail — Progrès</div>
            </td>
            <td style="text-align:center;vertical-align:middle;">
                <div style="font-size:13pt;font-weight:800;color:#6f42c1;">TRIBUNAL DE GRANDE INSTANCE HORS-CLASSE</div>
                <div style="font-size:11pt;font-weight:700;">DE NIAMEY</div>
                <div style="font-size:9pt;margin-top:3pt;">
                    SITUATION DES DOSSIERS CRPC
                </div>
                <div style="font-size:8pt;color:#555;margin-top:2pt;">
                    Période : <?= date('d/m/Y', strtotime($dateDebut)) ?> — <?= date('d/m/Y', strtotime($dateFin)) ?>
                    <?php if ($substitutNom): ?> | Substitut : <?= htmlspecialchars($substitutNom) ?><?php endif; ?>
                </div>
            </td>
            <td style="width:15%;text-align:right;font-size:7.5pt;color:#555;">
                Généré le<br><?= date('d/m/Y à H:i') ?>
            </td>
        </tr>
    </table>
</div>

<!-- ══ Statistiques de synthèse ══ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="crpc-stat-box">
            <div class="num"><?= $total ?></div>
            <div class="lbl">Dossiers CRPC total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="crpc-stat-box" style="border-color:#198754;">
            <div class="num" style="color:#198754;"><?= $byStatut['homologuee'] ?? 0 ?></div>
            <div class="lbl">Homologuées</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="crpc-stat-box" style="border-color:#dc3545;">
            <div class="num" style="color:#dc3545;"><?= $byStatut['refusee'] ?? 0 ?></div>
            <div class="lbl">Refusées</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="crpc-stat-box" style="border-color:#ffc107;">
            <div class="num" style="color:#b47d00;"><?= $byStatut['en_cours'] ?? 0 ?></div>
            <div class="lbl">En cours</div>
        </div>
    </div>
</div>

<?php if (empty($crpcList)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
        Aucun dossier CRPC trouvé pour cette période.
    </div>
</div>
<?php else: ?>

<!-- ══ Tableau récapitulatif global ══ -->
<div class="card border-0 shadow-sm mb-4 page-break-inside-avoid">
    <div class="card-header fw-semibold" style="background:#1a3c5e;color:#fff;">
        <i class="bi bi-table me-2"></i>Tableau récapitulatif — <?= $total ?> dossier(s) CRPC
    </div>
    <div class="table-responsive">
        <table class="crpc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>N° RG / PV</th>
                    <th>Date CRPC</th>
                    <th>Personne(s) poursuivie(s)</th>
                    <th>Infraction / Qualification</th>
                    <th>Texte applicable</th>
                    <th>Peine proposée</th>
                    <th>Avocat</th>
                    <th>Date audience homo.</th>
                    <th>Homologation</th>
                    <th>Peine homologuée</th>
                    <th>Substitut</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $statutLabels = [
                'en_cours'   => ['En cours',    'bg-warning text-dark'],
                'homologuee' => ['Homologuée',  'crpc-badge-homo text-white'],
                'refusee'    => ['Refusée',     'crpc-badge-refuse text-white'],
                'abandonnee' => ['Abandonnée',  'bg-secondary text-white'],
            ];
            foreach ($crpcList as $i => $d):
                [$sLbl,$sCls] = $statutLabels[$d['statut']] ?? [$d['statut'],'bg-secondary'];
                $personnes = $d['personnes'] ?? [];
                $nomPersonnes = implode(', ', array_map(fn($p) => strtoupper($p['nom_prenom']), $personnes));
                if (!$nomPersonnes) $nomPersonnes = '—';
            ?>
            <tr>
                <td class="text-muted text-center"><?= $i + 1 ?></td>
                <td>
                    <strong style="color:#1a3c5e;"><?= htmlspecialchars($d['pv_numero_rg'] ?? '—') ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($d['pv_numero_pv'] ?? '') ?></small>
                </td>
                <td class="text-nowrap"><?= $d['date_mise_en_oeuvre'] ? date('d/m/Y', strtotime($d['date_mise_en_oeuvre'])) : '—' ?></td>
                <td style="max-width:130px;"><?= htmlspecialchars($nomPersonnes) ?></td>
                <td style="max-width:140px;"><?= htmlspecialchars($d['qualification_faits'] ?? '—') ?></td>
                <td class="small text-muted" style="max-width:120px;"><?= htmlspecialchars($d['texte_applicable'] ?? '—') ?></td>
                <td>
                    <?= htmlspecialchars($d['peine_emprisonnement'] ?? '—') ?>
                    <?php if ($d['sursis_substitut']): ?><br><small class="badge bg-info text-dark">Sursis</small><?php endif; ?>
                    <?php if (!empty($d['amende_proposee'])): ?>
                    <br><small><?= number_format($d['amende_proposee'], 0, ',', ' ') ?> FCFA</small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($d['assistance_avocat']): ?>
                    <span class="badge bg-success">Oui</span><br>
                    <small><?= htmlspecialchars($d['nom_avocat'] ?? '') ?></small>
                    <?php elseif ($d['renonciation_avocat']): ?>
                    <span class="badge bg-secondary">Renonciation</span>
                    <?php else: ?>
                    —
                    <?php endif; ?>
                </td>
                <td class="text-nowrap"><?= $d['date_audience_homologation'] ? date('d/m/Y', strtotime($d['date_audience_homologation'])) : '—' ?></td>
                <td class="text-center">
                    <?php if ($d['homologation'] === '1' || $d['homologation'] === 1): ?>
                    <span class="badge crpc-badge-homo">✓ Oui</span>
                    <?php elseif ($d['homologation'] === '0' || $d['homologation'] === 0): ?>
                    <span class="badge crpc-badge-refuse">✗ Non</span>
                    <?php else: ?>
                    <span class="badge bg-secondary">Attente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?= htmlspecialchars($d['peine_emprisonnement_homo'] ?? '—') ?>
                    <?php if ($d['sursis_homologue']): ?><br><small class="badge bg-info text-dark">Sursis</small><?php endif; ?>
                    <?php if (!empty($d['amende_homologuee'])): ?>
                    <br><small><?= number_format($d['amende_homologuee'], 0, ',', ' ') ?> FCFA</small>
                    <?php endif; ?>
                </td>
                <td class="small"><?= htmlspecialchars($d['sub_prenom'].' '.$d['sub_nom']) ?></td>
                <td><span class="badge <?= $sCls ?>"><?= $sLbl ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ══ Fiches individuelles détaillées (une fiche = une CRPC) ══ -->
<div class="crpc-section-title mt-4 mb-3">
    <i class="bi bi-file-earmark-text me-2"></i>FICHES DÉTAILLÉES — <?= $total ?> DOSSIER(S) CRPC
</div>

<?php foreach ($crpcList as $idx => $d):
    $personnes = $d['personnes'] ?? [];
    [$sLbl,$sCls] = $statutLabels[$d['statut']] ?? [$d['statut'],'bg-secondary'];
?>
<div class="crpc-card <?= $idx > 0 ? 'page-break' : '' ?>">
    <!-- En-tête fiche -->
    <div class="crpc-card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-file-earmark-text me-2"></i>
            Dossier CRPC n° <?= $idx + 1 ?> — PV : <?= htmlspecialchars($d['pv_numero_rg'] ?? '—') ?>
        </span>
        <span class="badge <?= $sCls ?> ms-2"><?= $sLbl ?></span>
    </div>

    <div class="p-3">
        <!-- ── En-tête officielle imprimée ── -->
        <div class="text-center border-bottom pb-3 mb-3" style="border-bottom:2px solid #6f42c1 !important;">
            <div style="font-size:.7rem;color:#555;" class="mb-1">REPUBLIQUE DU NIGER — Fraternité — Travail — Progrès</div>
            <div style="font-size:.85rem;font-weight:800;color:#1a3c5e;">TRIBUNAL DE GRANDE INSTANCE HORS-CLASSE DE NIAMEY</div>
            <div style="font-size:.75rem;font-weight:700;color:#6f42c1;margin-top:2px;">
                PARQUET DU PROCUREUR DE LA REPUBLIQUE
            </div>
            <div style="font-size:.78rem;font-weight:700;margin-top:4px;border:1px solid #6f42c1;display:inline-block;padding:3px 14px;border-radius:4px;">
                FICHE CRPC — Comparution sur Reconnaissance Préalable de Culpabilité
            </div>
            <div style="font-size:.7rem;color:#777;margin-top:4px;">
                N° PV : <strong><?= htmlspecialchars($d['pv_numero_pv'] ?? '—') ?></strong> |
                N° RG : <strong><?= htmlspecialchars($d['pv_numero_rg'] ?? '—') ?></strong> |
                Date CRPC : <strong><?= $d['date_mise_en_oeuvre'] ? date('d/m/Y', strtotime($d['date_mise_en_oeuvre'])) : '—' ?></strong> |
                Substitut : <strong><?= htmlspecialchars($d['sub_prenom'].' '.$d['sub_nom']) ?></strong>
            </div>
        </div>

        <!-- Section I : Identification -->
        <div class="mb-3">
            <div class="crpc-section-title"><i class="bi bi-i-circle me-1"></i>I. IDENTIFICATION DU DOSSIER</div>
            <table class="crpc-table">
                <tr>
                    <th style="width:25%;">N° PV</th>
                    <td><?= htmlspecialchars($d['pv_numero_pv'] ?? '—') ?></td>
                    <th style="width:25%;">N° RG</th>
                    <td><?= htmlspecialchars($d['pv_numero_rg'] ?? '—') ?></td>
                </tr>
                <tr>
                    <th>Date de mise en œuvre</th>
                    <td><?= $d['date_mise_en_oeuvre'] ? date('d/m/Y', strtotime($d['date_mise_en_oeuvre'])) : '—' ?></td>
                    <th>Substitut ayant conduit</th>
                    <td><?= htmlspecialchars($d['sub_prenom'].' '.$d['sub_nom']) ?></td>
                </tr>
            </table>
        </div>

        <!-- Section II : Personnes poursuivies -->
        <div class="mb-3">
            <div class="crpc-section-title"><i class="bi bi-people me-1"></i>II. IDENTIFICATION DES PERSONNES POURSUIVIES</div>
            <?php if (empty($personnes)): ?>
            <p class="text-muted small fst-italic ps-2">Aucune personne enregistrée.</p>
            <?php else: ?>
            <table class="crpc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom et Prénom</th>
                        <th>Sexe</th>
                        <th>Âge</th>
                        <th>Nationalité</th>
                        <th>Profession</th>
                        <th>Quartier / Adresse</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($personnes as $n => $p): ?>
                <tr>
                    <td class="text-center"><?= $n + 1 ?></td>
                    <td><strong><?= htmlspecialchars(strtoupper($p['nom_prenom'])) ?></strong></td>
                    <td class="text-center"><?= htmlspecialchars($p['sexe'] ?? '—') ?></td>
                    <td class="text-center"><?= $p['age'] ? $p['age'].' ans' : '—' ?></td>
                    <td><?= htmlspecialchars($p['nationalite'] ?? 'Nigérienne') ?></td>
                    <td><?= htmlspecialchars($p['profession'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['quartier'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Section III : Infractions -->
        <div class="mb-3">
            <div class="crpc-section-title"><i class="bi bi-gavel me-1"></i>III. INFRACTION(S) POURSUIVIE(S)</div>
            <table class="crpc-table">
                <tr>
                    <th style="width:25%;">Qualification des faits</th>
                    <td colspan="3"><?= htmlspecialchars($d['qualification_faits'] ?? '—') ?></td>
                </tr>
                <tr>
                    <th>Date des faits</th>
                    <td><?= $d['date_faits'] ? date('d/m/Y', strtotime($d['date_faits'])) : '—' ?></td>
                    <th style="width:25%;">Texte applicable</th>
                    <td><?= htmlspecialchars($d['texte_applicable'] ?? '—') ?></td>
                </tr>
                <tr>
                    <th>Peine prévue par les textes</th>
                    <td colspan="3"><?= htmlspecialchars($d['peine_prevue'] ?? '—') ?></td>
                </tr>
            </table>
        </div>

        <!-- Section IV : Choix du conseil -->
        <div class="mb-3">
            <div class="crpc-section-title"><i class="bi bi-briefcase me-1"></i>IV. CHOIX DU CONSEIL</div>
            <table class="crpc-table">
                <tr>
                    <th style="width:30%;">Assistance d'un avocat</th>
                    <td style="width:20%;">
                        <?php if ($d['assistance_avocat']): ?>
                        <span class="badge bg-success">Oui</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Non</span>
                        <?php endif; ?>
                    </td>
                    <th style="width:30%;">Nom de l'avocat</th>
                    <td><?= htmlspecialchars($d['nom_avocat'] ?? '—') ?></td>
                </tr>
                <tr>
                    <th>Renonciation expresse à un avocat</th>
                    <td colspan="3">
                        <?php if ($d['renonciation_avocat']): ?>
                        <span class="badge bg-warning text-dark">Oui — Renonciation enregistrée</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Non</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Section V : Peine proposée -->
        <div class="mb-3">
            <div class="crpc-section-title"><i class="bi bi-balance-scale me-1"></i>V. PEINE PROPOSÉE PAR LE SUBSTITUT</div>
            <table class="crpc-table">
                <tr>
                    <th style="width:30%;">Peine d'emprisonnement proposée</th>
                    <td><?= htmlspecialchars($d['peine_emprisonnement'] ?? '—') ?></td>
                    <th style="width:20%;">Sursis proposé</th>
                    <td><?= $d['sursis_substitut'] ? '<span class="badge bg-info text-dark">Oui</span>' : '<span class="badge bg-secondary">Non</span>' ?></td>
                </tr>
                <tr>
                    <th>Amende proposée</th>
                    <td colspan="3">
                        <?= !empty($d['amende_proposee'])
                            ? '<strong>'.number_format($d['amende_proposee'], 0, ',', ' ').' FCFA</strong>'
                            : '—' ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Section VI : Homologation -->
        <div class="mb-3">
            <div class="crpc-section-title"><i class="bi bi-check2-square me-1"></i>VI. HOMOLOGATION DU PRÉSIDENT DU TRIBUNAL</div>
            <table class="crpc-table">
                <tr>
                    <th style="width:30%;">Date d'audience d'homologation</th>
                    <td><?= $d['date_audience_homologation'] ? date('d/m/Y', strtotime($d['date_audience_homologation'])) : '—' ?></td>
                    <th style="width:20%;">Homologation</th>
                    <td>
                        <?php if ($d['homologation'] === '1' || $d['homologation'] === 1): ?>
                        <span class="badge crpc-badge-homo" style="font-size:.85rem;">✓ OUI — Homologuée</span>
                        <?php elseif ($d['homologation'] === '0' || $d['homologation'] === 0): ?>
                        <span class="badge crpc-badge-refuse" style="font-size:.85rem;">✗ NON — Refusée</span>
                        <?php else: ?>
                        <span class="badge crpc-badge-attente" style="font-size:.85rem;">En attente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Peine d'emprisonnement homologuée</th>
                    <td><?= htmlspecialchars($d['peine_emprisonnement_homo'] ?? '—') ?></td>
                    <th>Sursis homologué</th>
                    <td><?= $d['sursis_homologue'] ? '<span class="badge bg-info text-dark">Oui</span>' : '<span class="badge bg-secondary">Non</span>' ?></td>
                </tr>
                <tr>
                    <th>Amende homologuée</th>
                    <td>
                        <?= !empty($d['amende_homologuee'])
                            ? '<strong>'.number_format($d['amende_homologuee'], 0, ',', ' ').' FCFA</strong>'
                            : '—' ?>
                    </td>
                    <th>Motif du refus</th>
                    <td><?= htmlspecialchars($d['motif_refus_homologation'] ?? '—') ?></td>
                </tr>
            </table>
        </div>

        <?php if (!empty($d['notes'])): ?>
        <!-- Notes complémentaires -->
        <div class="mb-2">
            <div class="crpc-section-title"><i class="bi bi-sticky me-1"></i>NOTES COMPLÉMENTAIRES</div>
            <div class="border rounded p-2 bg-light small fst-italic">
                <?= nl2br(htmlspecialchars($d['notes'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Signatures -->
        <div class="mt-4 pt-3 border-top">
            <div class="row g-3">
                <div class="col-md-6 text-center">
                    <div class="small fw-semibold text-muted mb-4">Le Substitut du Procureur de la République</div>
                    <div class="border-top mx-4 pt-1 text-muted small" style="margin-top:32px;">
                        Nom &amp; Signature
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <div class="small fw-semibold text-muted mb-4">Le Président du Tribunal</div>
                    <div class="border-top mx-4 pt-1 text-muted small" style="margin-top:32px;">
                        Nom &amp; Signature &amp; Cachet
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /p-3 -->
</div><!-- /crpc-card -->

<?php endforeach; ?>
<?php endif; ?>
