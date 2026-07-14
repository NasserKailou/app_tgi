<?php $pageTitle = 'Modifier la fiche CRPC — ' . htmlspecialchars($pv['numero_rg'] ?? ''); ?>
<style>
.crpc-edit-section { border-left: 4px solid #6f42c1; border-radius: 0 6px 6px 0; }
.crpc-section-header { background: #6f42c1; color: #fff; border-radius: 4px;
    padding: 6px 14px; font-weight: 700; font-size: .85rem; margin-bottom: 12px; }
.crpc-section-header i { margin-right: 6px; }
</style>

<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv">PVs</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>"><?= htmlspecialchars($pv['numero_rg']) ?></a></li>
            <li class="breadcrumb-item active">Modifier CRPC</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4 class="fw-bold mb-1" style="color:#6f42c1;">
                <i class="bi bi-file-earmark-text me-2"></i>Modifier la fiche CRPC
            </h4>
            <p class="text-muted mb-0 small">
                PV : <strong><?= htmlspecialchars($pv['numero_rg']) ?></strong>
                <?php if (!empty($pv['numero_pv'])): ?> — N° PV : <?= htmlspecialchars($pv['numero_pv']) ?><?php endif; ?>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour au PV
        </a>
    </div>
</div>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($flash['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash['success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
    <div class="card-header text-white fw-semibold d-flex align-items-center" style="background:#6f42c1;">
        <i class="bi bi-file-earmark-text me-2"></i>
        Fiche CRPC — Comparution sur Reconnaissance Préalable de Culpabilité
        <span class="badge bg-light text-dark ms-2 small fw-normal">TGI-HC Niamey</span>
    </div>
    <div class="card-body">

        <form method="POST" action="<?= BASE_URL ?>/crpc/update/<?= $crpc['id'] ?>">
            <?= CSRF::field() ?>

            <!-- ── Section I : Identification ── -->
            <div class="border rounded p-3 bg-white mb-4 crpc-edit-section">
                <div class="crpc-section-header">
                    <i class="bi bi-i-circle"></i>I. Identification du dossier
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Date de mise en œuvre de la CRPC</label>
                        <input type="date" name="crpc_date_mise_en_oeuvre" class="form-control"
                               value="<?= htmlspecialchars($crpc['date_mise_en_oeuvre'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">N° PV (référence)</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="<?= htmlspecialchars($pv['numero_pv'] ?? '—') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">N° RG (référence)</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="<?= htmlspecialchars($pv['numero_rg'] ?? '—') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Substitut ayant conduit la procédure</label>
                        <input type="text" name="crpc_substitut_nom" class="form-control"
                               value="<?= htmlspecialchars($crpc['substitut_nom_cache'] ?? (($pv['substitut_prenom']??'').' '.($pv['substitut_nom']??''))) ?>"
                               placeholder="Nom du substitut">
                    </div>
                </div>
            </div>

            <!-- ── Section II : Personnes poursuivies ── -->
            <div class="border rounded p-3 bg-white mb-4 crpc-edit-section">
                <div class="crpc-section-header">
                    <i class="bi bi-people"></i>II. Identification des personnes poursuivies
                </div>
                <div id="crpcPersonnes">
                    <?php
                    $personnes = $crpcPersonnes ?? [];
                    if (empty($personnes)) {
                        $personnes = [['id'=>'','mec_id'=>'','nom_prenom'=>'','sexe'=>'','age'=>'','nationalite'=>'Nigérienne','profession'=>'','quartier'=>'']];
                    }
                    foreach ($personnes as $n => $p):
                    ?>
                    <div class="crpc-personne border rounded p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold small text-secondary">Personne n° <?= $n + 1 ?></span>
                            <?php if ($n > 0): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCrpcPersonne(this)">
                                <i class="bi bi-x"></i> Supprimer
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="row g-2">
                            <input type="hidden" name="crpc_personne_id[]" value="<?= $p['id'] ?? '' ?>">
                            <input type="hidden" name="crpc_mec_id[]" value="<?= $p['mec_id'] ?? '' ?>">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Nom et Prénom <span class="text-danger">*</span></label>
                                <input type="text" name="crpc_nom_prenom[]" class="form-control"
                                       value="<?= htmlspecialchars($p['nom_prenom'] ?? '') ?>"
                                       placeholder="Nom complet en majuscules" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Sexe</label>
                                <select name="crpc_sexe[]" class="form-select">
                                    <option value="">—</option>
                                    <option value="M" <?= ($p['sexe']??'') === 'M' ? 'selected':'' ?>>M</option>
                                    <option value="F" <?= ($p['sexe']??'') === 'F' ? 'selected':'' ?>>F</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Âge</label>
                                <input type="number" name="crpc_age[]" class="form-control"
                                       value="<?= htmlspecialchars($p['age'] ?? '') ?>"
                                       placeholder="Âge" min="1" max="120">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Nationalité</label>
                                <input type="text" name="crpc_nationalite[]" class="form-control"
                                       value="<?= htmlspecialchars($p['nationalite'] ?? 'Nigérienne') ?>"
                                       placeholder="Nationalité">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Profession</label>
                                <input type="text" name="crpc_profession[]" class="form-control"
                                       value="<?= htmlspecialchars($p['profession'] ?? '') ?>"
                                       placeholder="Profession">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Quartier / Adresse</label>
                                <input type="text" name="crpc_quartier[]" class="form-control"
                                       value="<?= htmlspecialchars($p['quartier'] ?? '') ?>"
                                       placeholder="Quartier ou adresse">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addCrpcPersonne()">
                    <i class="bi bi-plus-circle me-1"></i>Ajouter une personne
                </button>
            </div>

            <!-- ── Section III : Infractions ── -->
            <div class="border rounded p-3 bg-white mb-4 crpc-edit-section">
                <div class="crpc-section-header">
                    <i class="bi bi-gavel"></i>III. Infraction(s) poursuivie(s)
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Qualification des faits <span class="text-danger">*</span></label>
                        <input type="text" name="crpc_qualification_faits" class="form-control"
                               value="<?= htmlspecialchars($crpc['qualification_faits'] ?? '') ?>"
                               placeholder="Ex : Vol aggravé, Escroquerie…" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Date des faits</label>
                        <input type="date" name="crpc_date_faits" class="form-control"
                               value="<?= htmlspecialchars($crpc['date_faits'] ?? '') ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Texte applicable <small class="text-muted">(articles, lois)</small></label>
                        <input type="text" name="crpc_texte_applicable" class="form-control"
                               value="<?= htmlspecialchars($crpc['texte_applicable'] ?? '') ?>"
                               placeholder="Ex : Art. 220 CP, Loi n°2015-08…">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Peine prévue par les textes</label>
                        <textarea name="crpc_peine_prevue" class="form-control" rows="2"
                                  placeholder="Ex : Emprisonnement de 1 à 5 ans et/ou amende de 50 000 à 500 000 FCFA"><?= htmlspecialchars($crpc['peine_prevue'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ── Section IV : Choix du conseil ── -->
            <div class="border rounded p-3 bg-white mb-4 crpc-edit-section">
                <div class="crpc-section-header">
                    <i class="bi bi-briefcase"></i>IV. Choix du conseil
                </div>
                <div class="row g-3 align-items-start">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="crpc_assistance_avocat" value="1"
                                   id="crpcAssistanceAvocat"
                                   <?= !empty($crpc['assistance_avocat']) ? 'checked' : '' ?>
                                   onchange="toggleAvocatNom(this.checked)">
                            <label class="form-check-label fw-semibold" for="crpcAssistanceAvocat">
                                Assistance d'un avocat
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="crpc_renonciation_avocat" value="1"
                                   id="crpcRenonciationAvocat"
                                   <?= !empty($crpc['renonciation_avocat']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="crpcRenonciationAvocat">
                                Renonciation expresse à un avocat
                            </label>
                        </div>
                    </div>
                    <div class="col-12" id="nomAvocatBlock"
                         style="display:<?= !empty($crpc['assistance_avocat']) ? 'block' : 'none' ?>">
                        <label class="form-label fw-semibold small">Nom de l'avocat</label>
                        <input type="text" name="crpc_nom_avocat" class="form-control"
                               value="<?= htmlspecialchars($crpc['nom_avocat'] ?? '') ?>"
                               placeholder="Maître…">
                    </div>
                </div>
            </div>

            <!-- ── Section V : Peine proposée ── -->
            <div class="border rounded p-3 bg-white mb-4 crpc-edit-section">
                <div class="crpc-section-header">
                    <i class="bi bi-balance-scale"></i>V. Peine proposée par le substitut
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Peine d'emprisonnement proposée</label>
                        <input type="text" name="crpc_peine_emprisonnement" class="form-control"
                               value="<?= htmlspecialchars($crpc['peine_emprisonnement'] ?? '') ?>"
                               placeholder="Ex : 18 mois dont 6 avec sursis">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Sursis proposé</label>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="crpc_sursis_substitut" value="1"
                                       id="sursisSub1" <?= !empty($crpc['sursis_substitut']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sursisSub1">Oui</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="crpc_sursis_substitut" value="0"
                                       id="sursisSub0" <?= empty($crpc['sursis_substitut']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sursisSub0">Non</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Amende proposée (FCFA)</label>
                        <div class="input-group">
                            <input type="number" name="crpc_amende_proposee" class="form-control"
                                   value="<?= htmlspecialchars($crpc['amende_proposee'] ?? '') ?>"
                                   placeholder="0" min="0" step="1000">
                            <span class="input-group-text">FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Section VI : Homologation ── -->
            <div class="border rounded p-3 bg-white mb-4 crpc-edit-section">
                <div class="crpc-section-header">
                    <i class="bi bi-check2-square"></i>VI. Homologation du Président du Tribunal
                    <span class="badge bg-light text-dark ms-2 small fw-normal">À compléter après audience</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">Date de l'audience d'homologation</label>
                        <input type="date" name="crpc_date_audience_homologation" class="form-control"
                               value="<?= htmlspecialchars($crpc['date_audience_homologation'] ?? '') ?>">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold small">Homologation</label>
                        <div class="d-flex gap-3 mt-2 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="crpc_homologation" value="1"
                                       id="homo1"
                                       <?= (isset($crpc['homologation']) && (string)$crpc['homologation'] === '1') ? 'checked' : '' ?>
                                       onchange="toggleHomoRefus(false)">
                                <label class="form-check-label fw-semibold text-success" for="homo1">✓ Oui — Homologuée</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="crpc_homologation" value="0"
                                       id="homo0"
                                       <?= (isset($crpc['homologation']) && (string)$crpc['homologation'] === '0') ? 'checked' : '' ?>
                                       onchange="toggleHomoRefus(true)">
                                <label class="form-check-label fw-semibold text-danger" for="homo0">✗ Non — Refusée</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="crpc_homologation" value=""
                                       id="homoNA"
                                       <?= (!isset($crpc['homologation']) || $crpc['homologation'] === null || $crpc['homologation'] === '') ? 'checked' : '' ?>
                                       onchange="toggleHomoRefus(false)">
                                <label class="form-check-label text-muted" for="homoNA">En attente</label>
                            </div>
                        </div>
                    </div>

                    <!-- Détails si homologuée -->
                    <div class="col-12" id="homoDetails"
                         <?php
                         $isHomo = isset($crpc['homologation']) && (string)$crpc['homologation'] === '1';
                         echo $isHomo ? '' : 'style="display:none"';
                         ?>>
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small">Peine d'emprisonnement homologuée</label>
                                <input type="text" name="crpc_peine_emprisonnement_homo" class="form-control"
                                       value="<?= htmlspecialchars($crpc['peine_emprisonnement_homo'] ?? '') ?>"
                                       placeholder="Ex : 12 mois ferme">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Sursis homologué</label>
                                <div class="d-flex gap-3 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="crpc_sursis_homologue" value="1"
                                               id="sursisH1" <?= !empty($crpc['sursis_homologue']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sursisH1">Oui</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="crpc_sursis_homologue" value="0"
                                               id="sursisH0" <?= empty($crpc['sursis_homologue']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sursisH0">Non</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Amende homologuée (FCFA)</label>
                                <div class="input-group">
                                    <input type="number" name="crpc_amende_homologuee" class="form-control"
                                           value="<?= htmlspecialchars($crpc['amende_homologuee'] ?? '') ?>"
                                           placeholder="0" min="0" step="1000">
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Motif refus si non-homologuée -->
                    <div class="col-12" id="homoRefusMotif"
                         <?php
                         $isRefus = isset($crpc['homologation']) && (string)$crpc['homologation'] === '0';
                         echo $isRefus ? '' : 'style="display:none"';
                         ?>>
                        <label class="form-label fw-semibold small text-danger">
                            Motif du refus <span class="text-danger">*</span>
                        </label>
                        <textarea name="crpc_motif_refus_homologation"
                                  class="form-control border-danger" rows="3"
                                  placeholder="Indiquer le motif du refus d'homologation…"><?= htmlspecialchars($crpc['motif_refus_homologation'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ── Statut CRPC ── -->
            <div class="border rounded p-3 bg-white mb-4 crpc-edit-section">
                <div class="crpc-section-header">
                    <i class="bi bi-flag"></i>Statut du dossier CRPC
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Statut</label>
                        <select name="crpc_statut" class="form-select">
                            <option value="en_cours"   <?= ($crpc['statut']??'') === 'en_cours'   ? 'selected':'' ?>>En cours</option>
                            <option value="homologuee" <?= ($crpc['statut']??'') === 'homologuee' ? 'selected':'' ?>>Homologuée</option>
                            <option value="refusee"    <?= ($crpc['statut']??'') === 'refusee'    ? 'selected':'' ?>>Refusée</option>
                            <option value="abandonnee" <?= ($crpc['statut']??'') === 'abandonnee' ? 'selected':'' ?>>Abandonnée</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ── Notes complémentaires ── -->
            <div class="border rounded p-3 bg-white mb-4 crpc-edit-section">
                <div class="crpc-section-header">
                    <i class="bi bi-sticky"></i>Notes complémentaires CRPC
                </div>
                <textarea name="crpc_notes" class="form-control" rows="3"
                          placeholder="Observations, circonstances particulières…"><?= htmlspecialchars($crpc['notes'] ?? '') ?></textarea>
            </div>

            <!-- ── Boutons d'action ── -->
            <div class="d-flex gap-3 justify-content-between align-items-center border-top pt-3">
                <a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Annuler
                </a>
                <button type="submit" class="btn btn-lg fw-semibold text-white" style="background:#6f42c1;border-color:#6f42c1;">
                    <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications CRPC
                </button>
            </div>

        </form>
    </div>
</div>

<script>
var crpcPersonneCount = <?= count($crpcPersonnes ?? [['']]) ?>;

function addCrpcPersonne() {
    crpcPersonneCount++;
    var n = crpcPersonneCount;
    var tpl = '<div class="crpc-personne border rounded p-2 mb-2 bg-light">' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<span class="fw-semibold small text-secondary">Personne n\u00b0 ' + n + '</span>' +
        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCrpcPersonne(this)">' +
        '<i class="bi bi-x"></i> Supprimer</button>' +
        '</div><div class="row g-2">' +
        '<input type="hidden" name="crpc_personne_id[]" value="">' +
        '<input type="hidden" name="crpc_mec_id[]" value="">' +
        '<div class="col-12"><label class="form-label small fw-semibold">Nom et Pr\u00e9nom <span class="text-danger">*</span></label>' +
        '<input type="text" name="crpc_nom_prenom[]" class="form-control" placeholder="Nom complet en majuscules" required></div>' +
        '<div class="col-md-2"><label class="form-label small">Sexe</label>' +
        '<select name="crpc_sexe[]" class="form-select"><option value="">—</option><option value="M">M</option><option value="F">F</option></select></div>' +
        '<div class="col-md-2"><label class="form-label small">\u00c2ge</label>' +
        '<input type="number" name="crpc_age[]" class="form-control" placeholder="\u00c2ge" min="1" max="120"></div>' +
        '<div class="col-md-4"><label class="form-label small">Nationalit\u00e9</label>' +
        '<input type="text" name="crpc_nationalite[]" class="form-control" value="Nig\u00e9rienne" placeholder="Nationalit\u00e9"></div>' +
        '<div class="col-md-4"><label class="form-label small">Profession</label>' +
        '<input type="text" name="crpc_profession[]" class="form-control" placeholder="Profession"></div>' +
        '<div class="col-md-6"><label class="form-label small">Quartier / Adresse</label>' +
        '<input type="text" name="crpc_quartier[]" class="form-control" placeholder="Quartier ou adresse"></div>' +
        '</div></div>';
    document.getElementById('crpcPersonnes').insertAdjacentHTML('beforeend', tpl);
}

function removeCrpcPersonne(btn) {
    btn.closest('.crpc-personne').remove();
}

function toggleAvocatNom(show) {
    document.getElementById('nomAvocatBlock').style.display = show ? 'block' : 'none';
}

function toggleHomoRefus(refus) {
    var det = document.getElementById('homoDetails');
    var ref = document.getElementById('homoRefusMotif');
    if (refus) {
        det.style.display = 'none';
        ref.style.display = 'block';
    } else {
        det.style.display = document.getElementById('homo1').checked ? 'block' : 'none';
        ref.style.display = 'none';
    }
}

// Initialiser l'affichage selon la valeur actuelle
document.addEventListener('DOMContentLoaded', function() {
    var homo1  = document.getElementById('homo1');
    var homo0  = document.getElementById('homo0');
    if (homo1 && homo1.checked) { toggleHomoRefus(false); }
    if (homo0 && homo0.checked) { toggleHomoRefus(true); }
});
</script>
