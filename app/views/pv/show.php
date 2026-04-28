<?php $pageTitle = 'Détail PV — ' . $pv['numero_rg']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv">PV</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($pv['numero_rg']) ?></li>
    </ol></nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-file-text me-2 text-primary"></i><?= htmlspecialchars($pv['numero_rg']) ?></h4>
            <p class="text-muted mb-0"><?= htmlspecialchars($pv['numero_pv']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if (Auth::hasRole(['admin','greffier','procureur'])): ?>
            <a href="<?= BASE_URL ?>/pv/edit/<?= $pv['id'] ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/export/pv/<?= $pv['id'] ?>" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-pdf me-1"></i>Imprimer / PDF</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Infos principales -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Informations générales</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><small class="text-muted d-block">Date du PV</small><strong><?= date('d/m/Y', strtotime($pv['date_pv'])) ?></strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Date de réception</small><strong><?= date('d/m/Y', strtotime($pv['date_reception'])) ?></strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Type d'affaire</small>
                        <span class="badge <?= $pv['type_affaire']==='penale'?'bg-danger':($pv['type_affaire']==='civile'?'bg-primary':'bg-success') ?> fs-6"><?= ucfirst($pv['type_affaire']) ?></span>
                        <?php if ($pv['est_antiterroriste']): ?><span class="badge bg-dark ms-1"><i class="bi bi-shield-exclamation"></i> Anti-terroriste</span><?php endif; ?>
                    </div>
                    <div class="col-md-6"><small class="text-muted d-block">Unité d'enquête</small><strong><?= htmlspecialchars($pv['unite_nom'] ?? '—') ?></strong></div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Infraction déclarée (unité d'enquête)</small>
                        <?php if (!empty($pv['infraction_libelle'])): ?>
                        <strong><?= htmlspecialchars($pv['infraction_libelle']) ?></strong>
                        <?php $catColors = ['criminelle'=>'danger','correctionnelle'=>'warning','contraventionnelle'=>'secondary']; ?>
                        <span class="badge bg-<?= $catColors[$pv['infraction_categorie']] ?? 'secondary' ?> ms-1"><?= ucfirst($pv['infraction_categorie'] ?? '') ?></span>
                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        <?php if (!empty($pvInfractions['unite'])): ?>
                        <div class="mt-1">
                        <?php foreach ($pvInfractions['unite'] as $inf): ?>
                        <span class="badge bg-light text-dark border me-1 mb-1">
                            <i class="bi bi-gavel me-1"></i><?= htmlspecialchars($inf['code']) ?> — <?= htmlspecialchars($inf['libelle']) ?>
                        </span>
                        <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($pv['qualification_substitut_libelle']) || !empty($pvInfractions['substitut'])): ?>
                    <div class="col-md-6">
                        <small class="text-muted d-block">
                            <i class="bi bi-scales me-1 text-success"></i>Qualification retenue
                            <span class="badge bg-success ms-1 small">Substitut</span>
                        </small>
                        <?php if (!empty($pv['qualification_substitut_libelle'])): ?>
                        <strong class="text-success"><?= htmlspecialchars($pv['qualification_substitut_libelle']) ?></strong>
                        <?php endif; ?>
                        <?php if (!empty($pv['qualification_details'])): ?>
                        <div class="small text-muted mt-1 fst-italic"><?= htmlspecialchars($pv['qualification_details']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($pvInfractions['substitut'])): ?>
                        <div class="mt-1">
                        <?php foreach ($pvInfractions['substitut'] as $inf): ?>
                        <span class="badge bg-success me-1 mb-1">
                            <?= htmlspecialchars($inf['code']) ?>
                            <?php if (!empty($inf['est_complicite'])): ?><i class="bi bi-people-fill ms-1" title="Complicité"></i><?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="col-12"><small class="text-muted d-block">Description des faits (initiale)</small><p class="mb-0"><?= nl2br(htmlspecialchars($pv['description_faits'] ?? '—')) ?></p></div>
                    <?php if (!empty($pv['lois_applicables'])): ?>
                    <div class="col-12">
                        <small class="text-muted d-block">
                            <i class="bi bi-book me-1 text-warning"></i>Lois applicables
                            <span class="badge bg-warning text-dark ms-1 small">Substitut</span>
                        </small>
                        <p class="mb-0 fw-semibold"><?= nl2br(htmlspecialchars($pv['lois_applicables'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($pv['est_antiterroriste']): ?>
        <!-- Section antiterroriste -->
        <div class="card border-danger border-0 shadow-sm mb-4" style="border-left: 4px solid #dc3545 !important">
            <div class="card-header text-white fw-semibold" style="background:#dc3545"><i class="bi bi-shield-exclamation me-2"></i>Informations antiterroristes</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted d-block">Région</small><strong><?= htmlspecialchars($pv['region_nom'] ?? '—') ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Département</small><strong><?= htmlspecialchars($pv['dept_nom'] ?? '—') ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Commune</small><strong><?= htmlspecialchars($pv['commune_nom'] ?? '—') ?></strong></div>
                    <div class="col-12">
                        <small class="text-muted d-block mb-2">Primo intervenants</small>
                        <?php if (!empty($pv['primo_intervenants'])): ?>
                        <?php foreach ($pv['primo_intervenants'] as $pi): ?>
                        <span class="badge bg-dark me-1 mb-1"><?= htmlspecialchars($pi['nom']) ?></span>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <span class="text-muted">Aucun renseigné</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

      <!-- ══════════ QUALIFICATION SUBSTITUT ══════════ -->
<?php if ($isSubstitut): ?>
<div class="card border-0 shadow-sm mb-4" style="border-left:4px solid #198754!important">
    <div class="card-header bg-success text-white fw-semibold">
        <i class="bi bi-scales me-2"></i>Qualification retenue & Lois applicables
        <span class="badge bg-light text-success ms-2 small">Réservé substitut</span>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/pv/update/<?= $pv['id'] ?>">
            <?= CSRF::field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Infractions qualifiées (multi-sélection)</label>
                    <div class="border rounded p-2" style="max-height:130px;overflow-y:auto;">
                        <?php foreach ($infractions as $inf):
                            $isChecked = false;
                            foreach (($pv['infractions_substitut'] ?? []) as $is) {
                                if ((int)$is['infraction_id'] === (int)$inf['id']) { $isChecked = true; break; }
                            }
                        ?>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox"
                                   name="infractions_substitut[]"
                                   value="<?= $inf['id'] ?>"
                                   id="qsub_<?= $inf['id'] ?>"
                                   <?= $isChecked ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="qsub_<?= $inf['id'] ?>">
                                <strong><?= htmlspecialchars($inf['code']) ?></strong>
                                — <?= htmlspecialchars($inf['libelle']) ?>
                                &nbsp;<input type="checkbox"
                                    name="complicite_<?= $inf['id'] ?>" value="1"
                                    <?= ($isChecked && ($pv['infractions_substitut'][array_search($inf['id'], array_column($pv['infractions_substitut'],'infraction_id'))]['est_complicite']??0)) ? 'checked' : '' ?>
                                    title="Complicité"> <small class="text-muted">complicité</small>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Précisions qualification (circonstances, etc.)</label>
                    <textarea name="qualification_details" class="form-control form-control-sm" rows="5"
                              placeholder="Ex: complicité, récidive, circonstances aggravantes..."><?= htmlspecialchars($pv['qualification_details'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">
                        <i class="bi bi-book me-1"></i>Lois applicables
                        <small class="text-muted">(textes de référence)</small>
                    </label>
                    <textarea name="lois_applicables" class="form-control form-control-sm" rows="3"
                              placeholder="Ex: Art. 250 CPP, Loi n°2016-XX..."><?= htmlspecialchars($pv['lois_applicables'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-check-lg me-1"></i>Enregistrer la qualification
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>



        <!-- ══════════ PVs LIÉS (MÊME RP) ══════════ -->
        <?php $pvsMemeRP = $pvLies ?? []; if (!empty($pvsMemeRP)): ?>
        <div class="card border-0 shadow-sm mb-4 border-info">
            <div class="card-header bg-info text-white fw-semibold">
                <i class="bi bi-link-45deg me-2"></i>PVs liés (même numéro RP : <?= htmlspecialchars($pv['numero_rp']) ?>)
                <span class="badge bg-light text-info ms-2"><?= count($pvsMemeRP) ?> autre(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>N° RG</th><th>N° PV</th><th>Date réception</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($pvsMemeRP as $pvLie): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/pv/show/<?= $pvLie['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($pvLie['numero_rg']) ?></a></td>
                        <td><?= htmlspecialchars($pvLie['numero_pv']) ?></td>
                        <td><?= date('d/m/Y', strtotime($pvLie['date_reception'])) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($pvLie['statut']) ?></span></td>
                        <td>
                        <?php if ($isSubstitut && !empty($dossier)): ?>
                        <form method="POST" action="<?= BASE_URL ?>/pv/fusionner/<?= $pv['id'] ?>"
                              style="display:inline"
                              onsubmit="return confirm('Fusionner ce PV dans le dossier actuel ?')">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="pv_ids[]" value="<?= $pvLie['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-outline-info btn-sm">
                                <i class="bi bi-arrows-merge me-1"></i>Fusionner
                            </button>
                        </form>
                        <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ══════════ PIÈCES JOINTES DU PV (Substitut) ══════════ -->
        <?php if ($isSubstitut): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
                <span><i class="bi bi-paperclip me-2 text-secondary"></i>Pièces jointes du PV</span>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalUploadPV">
                    <i class="bi bi-upload me-1"></i>Ajouter
                </button>
            </div>
            <div class="card-body p-0" id="pvDocsList">
            <?php if (empty($pvDocuments)): ?>

                <div class="text-center text-muted py-3 small">
                    <i class="bi bi-file-earmark-x display-6 d-block mb-1 opacity-25"></i>Aucune pièce jointe
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                <?php foreach ($pvDocuments as $doc): ?>
                <div class="list-group-item d-flex align-items-center gap-3">
                    <i class="bi bi-file-earmark-<?= str_contains($doc['mime_type']??'','pdf') ? 'pdf text-danger' : (str_contains($doc['mime_type']??'','image') ? 'image text-success' : 'text-secondary') ?> fs-4 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small"><?= htmlspecialchars($doc['nom_original'] ?? '') ?></div>
                        <div class="text-muted small">
                            <?= htmlspecialchars($doc['description'] ?? '') ?>
                            <?php if ($doc['taille']): ?>
                            — <?= round($doc['taille']/1024, 1) ?> Ko
                            <?php endif; ?>
                            — <?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?>
                            — par <?= htmlspecialchars(($doc['uploader_prenom']??'').' '.($doc['uploader_nom']??'')) ?>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/documents/view/<?= $doc['id'] ?>" target="_blank" class="btn btn-xs btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
                <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($dossier): ?>
        <!-- Dossier lié -->
        <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #198754 !important">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-folder2 me-2 text-success"></i>Dossier lié</div>
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <strong><?= htmlspecialchars($dossier['numero_rg']) ?></strong>
                    <?php if ($dossier['numero_rp']): ?><span class="badge bg-secondary ms-1"><?= htmlspecialchars($dossier['numero_rp']) ?></span><?php endif; ?>
                    <?php if ($dossier['numero_ri']): ?><span class="badge bg-info text-dark ms-1"><?= htmlspecialchars($dossier['numero_ri']) ?></span><?php endif; ?>
                    <div class="text-muted small mt-1"><?= htmlspecialchars($dossier['objet']) ?></div>
                </div>
                <a href="<?= BASE_URL ?>/dossiers/show/<?= $dossier['id'] ?>" class="btn btn-outline-success btn-sm">Voir le dossier <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        <?php endif; ?>

        <!-- ══════════ MISES EN CAUSE ══════════ -->
        <div class="card border-0 shadow-sm mb-4" id="mises-en-cause">
            <div class="card-header bg-white d-flex align-items-center justify-content-between fw-semibold">
                <span>
                    <i class="bi bi-person-exclamation me-2 text-warning"></i>
                    Mises en cause
                    <span class="badge bg-warning text-dark ms-2"><?= count($misesEnCause) ?></span>
                </span>
                <?php if (in_array($pv['statut'], ['recu','en_traitement']) && DroitsController::hasFuncAccess((int)($user['id']??0), 'mec_creer')): ?>
                <div class="d-flex gap-2">
                    <!-- Reconduire -->
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalReconduire">
                        <i class="bi bi-arrow-repeat me-1"></i>Reconduire
                    </button>
                    <!-- Ajouter -->
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalAddMEC">
                        <i class="bi bi-person-plus me-1"></i>Ajouter
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($misesEnCause)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-person-dash display-5 d-block mb-2 opacity-25"></i>
                    Aucune mise en cause enregistrée pour ce PV.
                </div>
                <?php else: ?>
                <!-- Onglets Poursuivi / Non poursuivi / En attente -->
                <?php
                $mec_poursuivi   = array_filter($misesEnCause, fn($m) => $m['decision_substitut'] === 'poursuivi');
                $mec_non_poursui = array_filter($misesEnCause, fn($m) => $m['decision_substitut'] === 'non_poursuivi');
                $mec_attente     = array_filter($misesEnCause, fn($m) => $m['decision_substitut'] === 'en_attente');
                ?>
                <ul class="nav nav-tabs nav-fill px-3 pt-2 border-0" id="mecTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mecAll">
                            <i class="bi bi-people me-1"></i>Tous (<?= count($misesEnCause) ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-success" data-bs-toggle="tab" data-bs-target="#mecPoursuivi">
                            <i class="bi bi-check-circle me-1"></i>Poursuivi(s) (<?= count($mec_poursuivi) ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-danger" data-bs-toggle="tab" data-bs-target="#mecNonPoursuivi">
                            <i class="bi bi-x-circle me-1"></i>Non poursuivi(s) (<?= count($mec_non_poursui) ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-secondary" data-bs-toggle="tab" data-bs-target="#mecAttente">
                            <i class="bi bi-hourglass me-1"></i>En attente (<?= count($mec_attente) ?>)
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-3">
                    <?php
                    $renderMecList = function(array $list, bool $active = false) use ($pv, $user): void {
                        $tabClass = $active ? 'show active' : '';
                        $tabId    = ''; // set by caller
                    ?>
                    <div class="list-group list-group-flush">
                    <?php foreach ($list as $mec): ?>
                    <?php
                    $decBadge = ['poursuivi'=>'bg-success','non_poursuivi'=>'bg-danger','en_attente'=>'bg-secondary'];
                    $decLabel = ['poursuivi'=>'Poursuivi','non_poursuivi'=>'Non poursuivi','en_attente'=>'En attente'];
                    $db = $decBadge[$mec['decision_substitut']] ?? 'bg-secondary';
                    $dl = $decLabel[$mec['decision_substitut']] ?? '—';
                    // Encode all MEC data for the popup
                    $mecJson = htmlspecialchars(json_encode([
                        'id'                     => $mec['id'],
                        'nom'                    => $mec['nom'] ?? '',
                        'prenom'                 => $mec['prenom'] ?? '',
                        'alias'                  => $mec['alias'] ?? '',
                        'nom_mere'               => $mec['nom_mere'] ?? '',
                        'date_naissance'         => $mec['date_naissance'] ?? '',
                        'lieu_naissance'         => $mec['lieu_naissance'] ?? '',
                        'nationalite'            => $mec['nationalite'] ?? '',
                        'sexe'                   => $mec['sexe'] ?? '',
                        'profession'             => $mec['profession'] ?? '',
                        'adresse'                => $mec['adresse'] ?? '',
                        'telephone'              => $mec['telephone'] ?? '',
                        'statut'                 => $mec['statut'] ?? '',
                        'statut_autre_detail'    => $mec['statut_autre_detail'] ?? '',
                        'photo'                  => $mec['photo'] ?? '',
                        'personne_contacter_nom' => $mec['personne_contacter_nom'] ?? '',
                        'personne_contacter_tel' => $mec['personne_contacter_tel'] ?? '',
                        'personne_contacter_lien'=> $mec['personne_contacter_lien'] ?? '',
                        'est_connu_archives'     => $mec['est_connu_archives'] ?? 0,
                        'nb_affaires_precedentes'=> $mec['nb_affaires_precedentes'] ?? 0,
                        'notes_antecedents'      => $mec['notes_antecedents'] ?? '',
                        'decision_substitut'     => $mec['decision_substitut'] ?? '',
                    ]), ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="list-group-item border-0 px-0 py-2">
                        <div class="d-flex align-items-start gap-3">
                            <?php if ($mec['photo']): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($mec['photo']) ?>"
                                 alt="" class="rounded" style="width:44px;height:55px;object-fit:cover;flex-shrink:0;cursor:pointer;"
                                 onclick="voirMEC(<?= $mecJson ?>)">
                            <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                 style="width:44px;height:55px;flex-shrink:0;cursor:pointer;"
                                 onclick="voirMEC(<?= $mecJson ?>)">
                                <i class="bi bi-person-fill text-muted fs-4"></i>
                            </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="text-uppercase"><?= htmlspecialchars($mec['nom']) ?></strong>
                                        <?= htmlspecialchars($mec['prenom']) ?>
                                        <?php if ($mec['alias']): ?><em class="text-muted small">(<?= htmlspecialchars($mec['alias']) ?>)</em><?php endif; ?>
                                        <span class="badge bg-light text-dark border ms-1 small"><?= ucfirst(str_replace('_', ' ', $mec['statut'])) ?></span>
                                        <?php if ($mec['est_connu_archives']): ?>
                                        <span class="badge bg-danger ms-1 small" title="Connu des archives"><i class="bi bi-exclamation-triangle-fill"></i> Récidiviste</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge <?= $db ?> ms-2"><?= $dl ?></span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <?php if ($mec['date_naissance']): ?><?= date('d/m/Y', strtotime($mec['date_naissance'])) ?><?php endif; ?>
                                    <?php if ($mec['lieu_naissance']): ?> — <?= htmlspecialchars($mec['lieu_naissance']) ?><?php endif; ?>
                                    <?php if ($mec['profession']): ?> | <?= htmlspecialchars($mec['profession']) ?><?php endif; ?>
                                </div>
                            </div>
                            <!-- Bouton Voir -->
                            <button type="button" class="btn btn-sm btn-outline-info" title="Voir les détails"
                                    onclick="voirMEC(<?= $mecJson ?>)">
                                <i class="bi bi-eye"></i> Voir
                            </button>
                            <!-- Actions sur MEC -->
                            <?php if (DroitsController::hasFuncAccess((int)($user['id']??0), 'mec_decision') && Auth::hasRole(['admin','procureur','substitut_procureur','president'])): ?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li>
                                        <a class="dropdown-item text-success" href="#"
                                           onclick="setDecision(<?= $mec['id'] ?>, 'poursuivi'); return false;">
                                            <i class="bi bi-check-circle me-2"></i>Marquer Poursuivi
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#"
                                           data-id="<?= $mec['id'] ?>"
                                           onclick="openNonPoursuite(this); return false;">
                                            <i class="bi bi-x-circle me-2"></i>Marquer Non poursuivi
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/pv/mise-en-cause/edit/<?= $mec['id'] ?>">
                                            <i class="bi bi-pencil me-2"></i>Modifier
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="<?= BASE_URL ?>/pv/mise-en-cause/delete/<?= $mec['id'] ?>"
                                              onsubmit="return confirm('Supprimer cette mise en cause ?')">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                    <?php };
                    ?>

                    <div class="tab-pane fade show active" id="mecAll">
                        <?php $renderMecList($misesEnCause); ?>
                    </div>
                    <div class="tab-pane fade" id="mecPoursuivi">
                        <?php if (empty($mec_poursuivi)): ?><p class="text-muted text-center py-3">Aucun mis en cause marqué comme poursuivi.</p><?php else: $renderMecList($mec_poursuivi); endif; ?>
                    </div>
                    <div class="tab-pane fade" id="mecNonPoursuivi">
                        <?php if (empty($mec_non_poursui)): ?><p class="text-muted text-center py-3">Aucun mis en cause non poursuivi.</p><?php else: $renderMecList($mec_non_poursui); endif; ?>
                    </div>
                    <div class="tab-pane fade" id="mecAttente">
                        <?php if (empty($mec_attente)): ?><p class="text-muted text-center py-3">Aucun en attente de décision.</p><?php else: $renderMecList($mec_attente); endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Colonne droite : workflow -->
    <div class="col-lg-4">
        <!-- Numéros officiels -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-hash me-2 text-primary"></i>Numéros officiels</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5">N° PV</dt>
                    <dd class="col-7 font-monospace"><?= htmlspecialchars($pv['numero_pv'] ?? '—') ?></dd>
                    <dt class="col-5">N° RG</dt>
                    <dd class="col-7 font-monospace fw-bold text-primary"><?= htmlspecialchars($pv['numero_rg'] ?? '—') ?></dd>
                    <dt class="col-5">N° Ordre</dt>
                    <dd class="col-7 font-monospace"><?= htmlspecialchars($pv['numero_ordre'] ?? '—') ?></dd>
                    <dt class="col-5">N° RP</dt>
                    <dd class="col-7 font-monospace text-success fw-semibold"><?= htmlspecialchars($pv['numero_rp'] ?? '—') ?></dd>
                    <?php if (!empty($pv['mode_poursuite'])): ?>
                    <dt class="col-5">Mode</dt>
                    <dd class="col-7">
                        <?php $mpLabels=['RI'=>'Réquisitoire Introductif','CD'=>'Citation Directe','FD'=>'Flagrant Délit','CRPC'=>'CRPC','autre'=>'Autre']; ?>
                        <span class="badge bg-info text-dark"><?= $mpLabels[$pv['mode_poursuite']] ?? $pv['mode_poursuite'] ?></span>
                    </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Statut -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-diagram-3 me-2"></i>Statut & Workflow</div>
            <div class="card-body">
                <?php
                $statutMap=['recu'=>['secondary','Reçu','Nouveau PV, en attente d\'affectation'],'en_traitement'=>['warning','En traitement','Affecté à un substitut'],'classe'=>['dark','Classé','Classé sans suite'],'transfere_instruction'=>['info','→ Instruction','Transféré en cabinet d\'instruction'],'transfere_jugement_direct'=>['success','→ Audience directe','Envoyé directement en audience']];
                [$cls,$lbl,$desc]=$statutMap[$pv['statut']]??['secondary',$pv['statut'],''];
                ?>
                <div class="text-center mb-3">
                    <span class="badge bg-<?= $cls ?> p-3 fs-6"><?= $lbl ?></span>
                    <p class="text-muted small mt-2"><?= $desc ?></p>
                </div>

                <?php if ($pv['substitut_id']): ?>
                <div class="mb-2"><small class="text-muted">Substitut assigné</small><br>
                    <strong><?= htmlspecialchars($pv['substitut_prenom'].' '.$pv['substitut_nom']) ?></strong><br>
                    <?php if ($pv['date_affectation_substitut']): ?>
                    <small class="text-muted">Depuis le <?= date('d/m/Y', strtotime($pv['date_affectation_substitut'])) ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($pv['statut'] === 'classe' && $pv['motif_classement']): ?>
                <div class="alert alert-secondary small mt-2 py-2"><?= htmlspecialchars($pv['motif_classement']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pv['statut'] === 'classe' && Auth::hasRole(['admin','procureur'])): ?>
        <div class="card border-0 shadow-sm mb-3 border-warning">
            <div class="card-header bg-warning fw-semibold">
                <i class="bi bi-arrow-counterclockwise me-2"></i>PV classé sans suite
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    <strong>Classé le :</strong> <?= $pv['date_classement'] ? date('d/m/Y', strtotime($pv['date_classement'])) : '—' ?>
                </p>
                <button class="btn btn-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalDeclasser">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Déclasser ce PV
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <?php if (in_array($pv['statut'], ['recu','en_traitement'])): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-play-circle me-2 text-primary"></i>Actions</div>
            <div class="card-body d-grid gap-2">

                <?php if (Auth::hasRole(['admin','procureur','president']) && $pv['statut']==='recu'): ?>
                <!-- Affecter substitut -->
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalAffecter">
                    <i class="bi bi-person-check me-2"></i>Affecter un substitut
                </button>
                <?php endif; ?>

                <?php if (Auth::hasRole(['admin','procureur','substitut_procureur']) && $pv['statut']==='en_traitement'): ?>
                <!-- Classer -->
                <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalClasser">
                    <i class="bi bi-archive me-2"></i>Classer sans suite
                </button>
                <!-- Transférer -->
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTransferer">
                    <i class="bi bi-send me-2"></i>Transférer
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Affecter -->
<div class="modal fade" id="modalAffecter" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Affecter un substitut</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="<?= BASE_URL ?>/pv/affecter/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <label class="form-label">Substitut du procureur</label>
                    <select name="substitut_id" class="form-select" required id="selectSubstitut">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($substituts as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="substitutChargeInfo" class="small text-muted mt-1"></div>
                    <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="suggererSubstitut()">
                        <i class="bi bi-magic me-1"></i>Suggérer le moins chargé
                    </button>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-warning">Affecter</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Classer -->
<div class="modal fade" id="modalClasser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Classer sans suite</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="<?= BASE_URL ?>/pv/classer/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <label class="form-label">Motif de classement <span class="text-danger">*</span></label>
                    <textarea name="motif_classement" class="form-control" rows="4" required placeholder="Indiquer le motif de classement..."></textarea>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-dark">Classer</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transférer (nouveau workflow basé sur le mode de poursuite) -->
<div class="modal fade" id="modalTransferer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-send me-2"></i>Transférer le PV — Décision du substitut</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/pv/transferer/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Règle :</strong> Seul le mode <strong>RI (Réquisitoire Introductif)</strong> envoie le dossier au cabinet d'instruction.
                        Les modes CD, FD, CRPC passent directement en audience.
                    </div>

                    <!-- Mode de poursuite -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mode de poursuite <span class="text-danger">*</span></label>
                        <select name="mode_poursuite" class="form-select" id="selectModePoursuite"
                                onchange="onModeChange(this.value)" required>
                            <option value="">— Sélectionner —</option>
                            <option value="RI">RI — Réquisitoire Introductif → Cabinet d'instruction</option>
                            <option value="CD">CD — Citation Directe → Audience directe</option>
                            <option value="FD">FD — Flagrant Délit → Audience directe</option>
                            <option value="CRPC">CRPC → Audience directe</option>
                            <option value="autre">Autre → Audience directe</option>
                        </select>
                    </div>

                    <!-- Cabinet d'instruction (visible seulement si RI) -->
                    <div id="cabinetBlock" style="display:none" class="mb-3 border rounded p-3 bg-light">
                        <label class="form-label fw-semibold"><i class="bi bi-building me-1 text-info"></i>Cabinet d'instruction <span class="text-danger">*</span></label>
                        <select name="cabinet_id" class="form-select" id="selectCabinet">
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($cabinets as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['numero'] . ' — ' . $c['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="cabinetChargeInfo" class="mt-1 small text-muted"></div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-1" onclick="suggererCabinet()">
                            <i class="bi bi-magic me-1"></i>Suggérer le moins chargé
                        </button>

                        <!-- Numéro RI : saisi manuellement par la greffière -->
                        <div class="mt-3">
                            <label class="form-label fw-semibold">
                                N° RI (Registre d'Instruction)
                                <span class="badge bg-warning text-dark ms-1">Saisi par la greffière</span>
                            </label>
                            <input type="text" name="numero_ri" class="form-control font-monospace"
                                   placeholder="ex: RI N°001/2026/INSTR (auto si vide)">
                            <div class="form-text">Laissez vide pour auto-génération. Le système vérifie l'unicité.</div>
                        </div>
                    </div>

                    <div id="audienceDirecteInfo" style="display:none" class="alert alert-success small mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        Ce dossier sera envoyé directement en audience (sans cabinet d'instruction).
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Objet du dossier <span class="text-danger">*</span></label>
                        <textarea name="objet" class="form-control" rows="3" required><?= htmlspecialchars($pv['description_faits']??'') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-2"></i>Transférer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function onModeChange(val) {
    var isRI   = (val === 'RI');
    document.getElementById('cabinetBlock').style.display       = isRI ? 'block' : 'none';
    document.getElementById('audienceDirecteInfo').style.display= (val && !isRI) ? 'block' : 'none';
}
function toggleCabinet(show){
    // legacy compat
}
function suggererCabinet(){
    fetch('<?= BASE_URL ?>/api/cabinets/charge')
    .then(r=>r.json())
    .then(data=>{
        if(data.success && data.data.length){
            var best = data.data[0];
            var sel  = document.getElementById('selectCabinet');
            if(sel){
                sel.value = best.id;
                document.getElementById('cabinetChargeInfo').innerHTML =
                    '<i class="bi bi-info-circle text-success me-1"></i>Suggéré : <strong>' +
                    best.numero + ' — ' + best.libelle + '</strong> (' +
                    best.nb_dossiers + ' dossier(s) actif(s))';
            }
        }
    }).catch(()=>{});
}
function suggererSubstitut(){
    fetch('<?= BASE_URL ?>/api/substituts/charge')
    .then(r=>r.json())
    .then(data=>{
        if(data.success && data.data.length){
            var best = data.data[0];
            var sel  = document.getElementById('selectSubstitut');
            if(sel){
                sel.value = best.id;
                var pvCount     = parseInt(best.nb_pvs)     || 0;
                var dosCount    = parseInt(best.nb_dossiers) || 0;
                document.getElementById('substitutChargeInfo').innerHTML =
                    '<i class="bi bi-info-circle text-success me-1"></i>' +
                    'Suggéré : <strong>' + best.prenom + ' ' + best.nom + '</strong> — ' +
                    '<span class="text-primary">' + pvCount + ' PV(s) en cours</span>' +
                    (dosCount > 0 ? ' — <span class="text-info">' + dosCount + ' dossier(s) actif(s)</span>' : '');
            }
        } else {
            document.getElementById('substitutChargeInfo').innerHTML =
                '<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Aucun substitut disponible.</span>';
        }
    }).catch(()=>{
        document.getElementById('substitutChargeInfo').innerHTML =
            '<span class="text-danger"><i class="bi bi-wifi-off me-1"></i>Erreur de connexion.</span>';
    });
}
</script>

<!-- ══ Modal Upload Document PV ══ -->
<div class="modal fade" id="modalUploadPV" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Ajouter une pièce jointe au PV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Fichier <span class="text-danger">*</span></label>
                    <input type="file" id="pvDocFile" class="form-control"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.odt">
                    <div class="form-text">PDF, DOC, DOCX, JPG, PNG, XLSX, ODT — max 10 Mo</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description (optionnel)</label>
                    <input type="text" id="pvDocDesc" class="form-control" placeholder="Ex: Rapport d'expertise…">
                </div>
                <div id="pvUploadProgress" style="display:none">
                    <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%"></div></div>
                </div>
                <div id="pvUploadMsg" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnPVUpload">
                    <i class="bi bi-upload me-1"></i>Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ Modal Voir MEC ══ -->
<div class="modal fade" id="modalVoirMEC" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Détails — Mise en cause</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="voirMecBody">
                <!-- Rempli dynamiquement par JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- ══ Modal Ajouter Mise en cause ══ -->
<div class="modal fade" id="modalAddMEC" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Ajouter une mise en cause</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 overflow-auto" style="flex:1 1 auto;">
                <?php
                $formAction    = BASE_URL . '/pv/mise-en-cause/store/' . $pv['id'];
                $pvId          = $pv['id'];
                $btnLabel      = 'Enregistrer la mise en cause';
                $mec           = null;
                $inModal       = true;
                $mecInfractions = ['unite' => [], 'substitut' => []];
                // Inclusion du formulaire partagé (_form.php contient sa propre balise <form>)
                include __DIR__ . '/../mises_en_cause/_form.php';
                $inModal = false;
                ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ Modal Reconduire mise en cause ══ -->
<div class="modal fade" id="modalReconduire" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Reconduire une mise en cause</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/pv/mise-en-cause/reconduire/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Recherchez une mise en cause enregistrée dans un ancien PV pour la rattacher à ce dossier.
                        Elle sera copiée avec ses informations et son compteur d'affaires sera incrémenté.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rechercher par nom / prénom / alias</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="mecSearchInput" class="form-control"
                                   placeholder="Saisir au moins 2 caractères..."
                                   autocomplete="off"
                                   oninput="searchMEC(this.value)">
                            <button type="button" class="btn btn-outline-secondary" onclick="clearMecSearch()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div id="mecSearchResults" class="list-group mb-3"></div>
                    <input type="hidden" name="mec_source_id" id="mecSourceId">
                    <!-- Fiche détail de la MEC sélectionnée -->
                    <div id="mecSelectedCard" style="display:none" class="border rounded p-3 bg-light">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-check-circle-fill me-2 text-success"></i>Mise en cause sélectionnée</h6>
                        <div class="d-flex gap-3">
                            <div id="mecCardPhoto" class="flex-shrink-0"></div>
                            <div class="flex-grow-1" id="mecCardDetails"></div>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted" id="mecCardPV"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btnReconduire" disabled>
                        <i class="bi bi-arrow-repeat me-1"></i>Reconduire cette personne
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ Forms décision substitut (cachés) ══ -->
<form id="formDecisionMEC" method="POST" style="display:none">
    <?= CSRF::field() ?>
    <input type="hidden" name="decision_substitut" id="decisionValue">
    <input type="hidden" name="motif_non_poursuite" id="motifValue">
</form>

<!-- ══ Modal Non-poursuite motif ══ -->
<div class="modal fade" id="modalNonPoursuite" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Motif de non-poursuite</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formNonPoursuite">
                <?= CSRF::field() ?>
                <input type="hidden" name="decision_substitut" value="non_poursuivi">
                <div class="modal-body">
                    <label class="form-label fw-semibold">Motif de non-poursuite</label>
                    <textarea name="motif_non_poursuite" class="form-control" rows="3"
                              placeholder="Indiquer le motif..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Déclasser PV -->
<div class="modal fade" id="modalDeclasser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Déclasser le PV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/pv/declasser/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Le PV sera remis au statut <strong>En traitement</strong> pour reprise du dossier.
                    </div>
                    <?php if ($pv['motif_classement']): ?>
                    <div class="mb-3">
                        <small class="text-muted">Motif du classement initial :</small>
                        <p class="fst-italic small"><?= htmlspecialchars($pv['motif_classement']) ?></p>
                    </div>
                    <?php endif; ?>
                    <label class="form-label fw-bold">Motif du déclassement <span class="text-danger">*</span></label>
                    <textarea name="motif_declassement" class="form-control" rows="4" required
                              placeholder="Exposez les raisons du déclassement (nouveaux éléments, erreur, …)"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Confirmer le déclassement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Décision substitut sur mise en cause ──────────────────────────────
function setDecision(mecId, decision) {
    if (!confirm('Marquer cette mise en cause comme "' + (decision === 'poursuivi' ? 'Poursuivi' : 'Non poursuivi') + '" ?')) return;
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = '<?= BASE_URL ?>/pv/mise-en-cause/decision/' + mecId;
    f.innerHTML = document.querySelector('[name="_csrf"]').outerHTML + '<input type="hidden" name="decision_substitut" value="' + decision + '">';
    document.body.appendChild(f);
    f.submit();
}

function openNonPoursuite(el) {
    var mecId = el.dataset.id;
    var frm   = document.getElementById('formNonPoursuite');
    frm.action = '<?= BASE_URL ?>/pv/mise-en-cause/decision/' + mecId;
    var modal  = new bootstrap.Modal(document.getElementById('modalNonPoursuite'));
    modal.show();
}

// ── Recherche MEC pour reconduction ──────────────────────────────────
// ── Voir fiche MEC (popup) ─────────────────────────────────────────────
function voirMEC(m) {
    var baseUrl = '<?= BASE_URL ?>';
    var photoHtml = m.photo
        ? '<img src="' + baseUrl + '/' + m.photo + '" alt="Photo" class="img-thumbnail" style="width:130px;height:160px;object-fit:cover;">'
        : '<div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:130px;height:160px;"><i class="bi bi-person-fill text-muted" style="font-size:3.5rem;"></i></div>';
    var statutLabels = {'mise_en_cause':'Mise en cause','prevenu':'Prévenu','temoin':'Témoin','autre':'Autre'};
    var decLabels = {
        'poursuivi':'<span class="badge bg-success">Poursuivi</span>',
        'non_poursuivi':'<span class="badge bg-danger">Non poursuivi</span>',
        'en_attente':'<span class="badge bg-secondary">En attente</span>'
    };
    var rows = [
        ['Nom', '<strong class="text-uppercase fs-6">' + (m.nom||'—') + '</strong>'],
        ['Prénom', m.prenom||'—'],
        ['Alias / Surnom', m.alias||'—'],
        ['Nom de la mère', m.nom_mere||'—'],
        ['Date de naissance', m.date_naissance ? new Date(m.date_naissance).toLocaleDateString('fr-FR') : '—'],
        ['Lieu de naissance', m.lieu_naissance||'—'],
        ['Nationalité', m.nationalite||'—'],
        ['Sexe', m.sexe === 'F' ? 'Féminin' : (m.sexe === 'M' ? 'Masculin' : '—')],
        ['Profession', m.profession||'—'],
        ['Adresse', m.adresse||'—'],
        ['Téléphone', m.telephone||'—'],
        ['Statut', statutLabels[m.statut] || m.statut || '—'],
        ['Décision substitut', decLabels[m.decision_substitut] || '—'],
        ['Connu des archives', m.est_connu_archives ? '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Oui — Récidiviste</span>' : '<span class="badge bg-success">Non</span>'],
        ["Nbre d'affaires préc.", m.nb_affaires_precedentes || '0'],
    ];
    if (m.personne_contacter_nom) {
        rows.push(['Personne à contacter', m.personne_contacter_nom +
            (m.personne_contacter_tel ? ' — ' + m.personne_contacter_tel : '') +
            (m.personne_contacter_lien ? ' (' + m.personne_contacter_lien + ')' : '')]);
    }
    if (m.notes_antecedents) {
        rows.push(['Antécédents / Notes', '<em class="text-muted">' + m.notes_antecedents + '</em>']);
    }
    var tableHtml = '<table class="table table-sm table-bordered mb-0 small">';
    rows.forEach(function(r) {
        tableHtml += '<tr><th class="table-secondary" style="width:38%;white-space:nowrap">' + r[0] + '</th><td>' + r[1] + '</td></tr>';
    });
    tableHtml += '</table>';
    document.getElementById('voirMecBody').innerHTML =
        '<div class="d-flex gap-4 align-items-start flex-wrap">' +
        '<div class="flex-shrink-0 text-center">' + photoHtml + '</div>' +
        '<div class="flex-grow-1">' + tableHtml + '</div>' +
        '</div>';
    new bootstrap.Modal(document.getElementById('modalVoirMEC')).show();
}

// ── Recherche MEC pour reconduction ──────────────────────────────────
var mecSearchTimer = null;
function searchMEC(q) {
    clearTimeout(mecSearchTimer);
    var resultsDiv = document.getElementById('mecSearchResults');
    if (q.length < 2) { resultsDiv.innerHTML = ''; return; }
    mecSearchTimer = setTimeout(function() {
        fetch('<?= BASE_URL ?>/api/mises-en-cause/search?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            var html = '';
            if (data.success && data.data.length) {
                data.data.forEach(function(m) {
                    html += '<a href="#" class="list-group-item list-group-item-action" ' +
                        "onclick='selectMEC(" + m.id + ", " + JSON.stringify(m).replace(/'/g, "&#39;") + "); return false;'>" +
                        '<div class="d-flex justify-content-between align-items-center">' +
                        '<div><strong class="text-uppercase">' + (m.nom||'') + '</strong> ' + (m.prenom||'') +
                        (m.alias ? ' <em class="text-muted small">(' + m.alias + ')</em>' : '') +
                        (m.est_connu_archives ? ' <span class="badge bg-danger ms-1 small">Récidiviste</span>' : '') +
                        '<br><small class="text-muted">' + (m.profession||'') + (m.lieu_naissance ? ' — ' + m.lieu_naissance : '') + '</small></div>' +
                        '<span class="badge bg-light text-dark border small">PV ' + (m.numero_rg||'?') + '</span>' +
                        '</div></a>';
                });
            } else {
                html = '<div class="list-group-item text-muted text-center py-2 small"><i class="bi bi-search me-1"></i>Aucune mise en cause trouvée</div>';
            }
            resultsDiv.innerHTML = html;
        }).catch(function(e){ console.warn('Erreur recherche MEC:', e); });
    }, 300);
}

function selectMEC(id, m) {
    document.getElementById('mecSourceId').value = id;
    document.getElementById('mecSearchResults').innerHTML = '';
    document.getElementById('mecSearchInput').value = (m.nom||'') + ' ' + (m.prenom||'');
    document.getElementById('btnReconduire').disabled = false;

    var baseUrl = '<?= BASE_URL ?>';
    var photoHtml = m.photo
        ? '<img src="' + baseUrl + '/' + m.photo + '" alt="Photo" class="rounded" style="width:80px;height:100px;object-fit:cover;">'
        : '<div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" style="width:80px;height:100px;"><i class="bi bi-person-fill fs-2"></i></div>';
    document.getElementById('mecCardPhoto').innerHTML = photoHtml;

    var statutLabels = {'mise_en_cause':'Mise en cause','prevenu':'Prévenu','temoin':'Témoin','autre':'Autre'};
    var detailsHtml =
        '<p class="mb-1"><strong class="text-uppercase fs-6">' + (m.nom||'') + '</strong> ' + (m.prenom||'') +
        (m.alias ? ' <em class="text-muted">(' + m.alias + ')</em>' : '') + '</p>' +
        '<p class="mb-1 small"><i class="bi bi-calendar me-1 text-muted"></i>' +
        (m.date_naissance ? new Date(m.date_naissance).toLocaleDateString('fr-FR') : '—') +
        (m.lieu_naissance ? ' — ' + m.lieu_naissance : '') + '</p>' +
        (m.profession ? '<p class="mb-1 small"><i class="bi bi-briefcase me-1 text-muted"></i>' + m.profession + '</p>' : '') +
        (m.nationalite ? '<p class="mb-1 small"><i class="bi bi-flag me-1 text-muted"></i>' + m.nationalite + '</p>' : '') +
        '<p class="mb-0"><span class="badge bg-warning text-dark">' + (statutLabels[m.statut]||m.statut||'—') + '</span>' +
        (m.est_connu_archives ? ' <span class="badge bg-danger ms-1">Récidiviste</span>' : '') +
        (m.nb_affaires_precedentes > 0 ? ' <span class="badge bg-secondary ms-1">' + m.nb_affaires_precedentes + ' affaire(s) préc.</span>' : '') + '</p>';
    document.getElementById('mecCardDetails').innerHTML = detailsHtml;
    document.getElementById('mecCardPV').innerHTML =
        '<i class="bi bi-file-text me-1"></i>Issu du PV n° <strong>' + (m.numero_rg||'?') + '</strong>' +
        (m.date_reception ? ' — reçu le ' + new Date(m.date_reception).toLocaleDateString('fr-FR') : '');
    document.getElementById('mecSelectedCard').style.display = 'block';
}

function clearMecSearch() {
    document.getElementById('mecSearchInput').value = '';
    document.getElementById('mecSearchResults').innerHTML = '';
    document.getElementById('mecSourceId').value = '';
    document.getElementById('mecSelectedCard').style.display = 'none';
    document.getElementById('btnReconduire').disabled = true;
}

var reconduireModal = document.getElementById('modalReconduire');
if (reconduireModal) {
    reconduireModal.addEventListener('hidden.bs.modal', clearMecSearch);
}

// ── Upload pièce jointe PV ────────────────────────────────────────────
document.getElementById('btnPVUpload')?.addEventListener('click', function() {
    var file = document.getElementById('pvDocFile').files[0];
    if (!file) { alert('Sélectionnez un fichier.'); return; }
    var desc = document.getElementById('pvDocDesc').value;
    var fd   = new FormData();
    fd.append('fichier', file);
    fd.append('description', desc);
    fd.append('_csrf', document.querySelector('[name="_csrf"]')?.value || '');
    document.getElementById('pvUploadProgress').style.display = 'block';
    document.getElementById('pvUploadMsg').innerHTML = '';
    document.getElementById('btnPVUpload').disabled = true;
    fetch('<?= BASE_URL ?>/pv/upload/<?= $pv['id'] ?>', {method:'POST', body:fd})
    .then(r=>r.json())
    .then(data=>{
        document.getElementById('pvUploadProgress').style.display = 'none';
        document.getElementById('btnPVUpload').disabled = false;
        if (data.error) {
            document.getElementById('pvUploadMsg').innerHTML = '<div class="alert alert-danger small py-2">'+data.error+'</div>';
        } else {
            document.getElementById('pvUploadMsg').innerHTML = '<div class="alert alert-success small py-2"><i class="bi bi-check-circle me-1"></i>'+data.message+'</div>';
            document.getElementById('pvDocFile').value = '';
            document.getElementById('pvDocDesc').value = '';
            setTimeout(()=>location.reload(), 1200);
        }
    }).catch(e=>{
        document.getElementById('pvUploadProgress').style.display = 'none';
        document.getElementById('btnPVUpload').disabled = false;
        document.getElementById('pvUploadMsg').innerHTML = '<div class="alert alert-danger small py-2">Erreur réseau</div>';
    });
});
</script>