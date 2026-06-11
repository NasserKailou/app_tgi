<?php $pageTitle = 'Modifier le PV'; ?>
<style>
/* ── Infraction cochée : fond bleu marine, texte blanc (identique à create.php) ── */
.infraction-row.infraction-checked {
    background-color: #1a3c5e !important;
    color: #ffffff !important;
}
.infraction-row.infraction-checked .form-check-label,
.infraction-row.infraction-checked .fw-semibold,
.infraction-row.infraction-checked .text-muted {
    color: #ffffff !important;
}
.infraction-row.infraction-checked .form-check-input {
    border-color: #a8c4e0;
}
.infraction-row.infraction-checked .badge.bg-warning { color:#fff !important; }
</style>

<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/pv">Procès-Verbaux</a></li>
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/pv/show/<?=$pv['id']?>"><?=htmlspecialchars($pv['numero_rg'])?></a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol></nav>
    <h4 class="fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Modifier le PV</h4>
</div>

<?php
// ── Récupération des IDs d'infractions pré-cochées ─────────────────────────
// Priorité 1 : POST (re-soumission avec erreur)
// Priorité 2 : données existantes en DB via getPVDetail() → $pv['infractions_unite']
if (isset($_POST['infractions_unite'])) {
    $selectedInfr = array_map('intval', (array)$_POST['infractions_unite']);
} else {
    // Extraire les infraction_id depuis le tableau d'objets infractions_unite
    $selectedInfr = array_map(
        fn($i) => (int)($i['infraction_id'] ?? $i['id'] ?? 0),
        (array)($pv['infractions_unite'] ?? [])
    );
}

if (isset($_POST['est_complicite'])) {
    $selectedComp = array_map('intval', (array)$_POST['est_complicite']);
} else {
    // Extraire les infraction_id dont est_complicite = 1
    $selectedComp = array_map(
        fn($i) => (int)($i['infraction_id'] ?? $i['id'] ?? 0),
        array_filter((array)($pv['infractions_unite'] ?? []), fn($i) => !empty($i['est_complicite']))
    );
}
$selectedInfr = array_values(array_filter($selectedInfr));
$selectedComp = array_values(array_filter($selectedComp));
?>

<div class="row justify-content-center"><div class="col-lg-10">
<form method="POST" action="<?=BASE_URL?>/pv/update/<?=$pv['id']?>" novalidate>
    <?=CSRF::field()?>
    <!-- Identifie ce formulaire comme édition générale -->
    <!-- update() mettra à jour données générales + infractions_unite uniquement -->
    <input type="hidden" name="_form_context" value="edit_general">

    <!-- ============================== -->
    <!-- Informations générales         -->
    <!-- ============================== -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Informations générales</div>
        <div class="card-body"><div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">N° PV d'origine <span class="text-danger">*</span></label>
                <input type="text" name="numero_pv" class="form-control" required
                       value="<?= htmlspecialchars($_POST['numero_pv'] ?? $pv['numero_pv']) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">N° d'ordre du PV <span class="text-muted small">(saisi manuellement)</span></label>
                <input type="text" name="numero_ordre" class="form-control font-monospace"
                       value="<?= htmlspecialchars($_POST['numero_ordre'] ?? $pv['numero_ordre'] ?? '') ?>"
                       placeholder="ex: 001/2026">
            </div>

            <div class="col-md-4">
                <label class="form-label">
                    N° RP (Registre du Parquet)
                    <span class="text-muted small">(unique, saisi manuellement)</span>
                </label>
                <input type="text" name="numero_rp" class="form-control font-monospace text-primary fw-semibold"
                       value="<?= htmlspecialchars($_POST['numero_rp'] ?? $pv['numero_rp'] ?? '') ?>"
                       placeholder="ex: RP N°001/2026/PARQUET">
                <div class="form-text">Le système vérifie l'unicité du RP.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="bi bi-tag me-1 text-primary"></i>Type d'affaire — Pôle <span class="text-danger">*</span>
                </label>
                <?php $typeAff = $_POST['type_affaire'] ?? $pv['type_affaire'] ?? 'droit_commun_majeur'; ?>
                <select name="type_affaire" class="form-select" required>
                    <optgroup label="Droit Commun">
                        <option value="droit_commun_mineur"   <?= $typeAff === 'droit_commun_mineur'   ? 'selected' : '' ?>>Droit Commun — Mineur</option>
                        <option value="droit_commun_majeur"   <?= $typeAff === 'droit_commun_majeur'   ? 'selected' : '' ?>>Droit Commun — Majeur</option>
                    </optgroup>
                    <optgroup label="Pôle Antiterroriste">
                        <option value="pole_antiterro_mineur" <?= $typeAff === 'pole_antiterro_mineur' ? 'selected' : '' ?>>Pôle Antiterroriste — Mineur</option>
                        <option value="pole_antiterro_majeur" <?= $typeAff === 'pole_antiterro_majeur' ? 'selected' : '' ?>>Pôle Antiterroriste — Majeur</option>
                    </optgroup>
                    <optgroup label="Pôle Économique">
                        <option value="pole_economique"       <?= $typeAff === 'pole_economique'       ? 'selected' : '' ?>>Pôle Économique et Financier</option>
                    </optgroup>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Date du PV</label>
                <input type="date" name="date_pv" class="form-control"
                       value="<?= htmlspecialchars($_POST['date_pv'] ?? $pv['date_pv']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Date de réception</label>
                <input type="date" name="date_reception" class="form-control"
                       value="<?= htmlspecialchars($_POST['date_reception'] ?? $pv['date_reception']) ?>">
            </div>

            <div class="col-12">
                <label class="form-label">Unité d'enquête</label>
                <select name="unite_enquete_id" class="form-select">
                    <option value="">— Sélectionner —</option>
                    <?php
                    $uniteSel = $_POST['unite_enquete_id'] ?? $pv['unite_enquete_id'] ?? '';
                    foreach($unites as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $uniteSel == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nom']) ?> (<?= htmlspecialchars($u['type']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ============================================================ -->
            <!-- INFRACTIONS MULTIPLES (unité d'enquête)                      -->
            <!-- Header navy identique à create.php — texte blanc visible     -->
            <!-- ============================================================ -->
            <div class="col-12">
                <div class="card border-2" style="border-color:#1a3c5e !important;">
                    <div class="card-header d-flex align-items-center justify-content-between" style="background:#1a3c5e;">
                        <span class="fw-semibold" style="color:#ffffff;">
                            <i class="bi bi-list-check me-2"></i>Types d'infractions retenues
                            <span class="small fw-normal" style="color:#a8c4e0;">(infractions déclarées — unité d'enquête)</span>
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark" id="infractionsCount">0</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Recherche -->
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="infractionSearch" class="form-control"
                                   placeholder="Rechercher une infraction par libellé ou code...">
                            <button type="button" class="btn btn-outline-secondary" id="btnClearSearch">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        <!-- Filtres par catégorie -->
                        <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                            <input type="radio" class="btn-check" name="filterCat" id="catAll" value="" checked>
                            <label class="btn btn-outline-secondary" for="catAll">Toutes</label>

                            <input type="radio" class="btn-check" name="filterCat" id="catCrim" value="criminelle">
                            <label class="btn btn-outline-danger" for="catCrim">Criminelles</label>

                            <input type="radio" class="btn-check" name="filterCat" id="catCorr" value="correctionnelle">
                            <label class="btn btn-outline-warning" for="catCorr">Correctionnelles</label>

                            <input type="radio" class="btn-check" name="filterCat" id="catCont" value="contraventionnelle">
                            <label class="btn btn-outline-info" for="catCont">Contraventionnelles</label>

                            <input type="radio" class="btn-check" name="filterCat" id="catSel" value="selected">
                            <label class="btn btn-outline-success" for="catSel">
                                <i class="bi bi-check-square"></i> Sélectionnées
                            </label>
                        </div>

                        <!-- Liste scrollable -->
                        <div class="border rounded p-2" id="infractionsList"
                             style="max-height: 320px; overflow-y: auto; background:#ffffff;">
                            <?php foreach ($infractions as $inf):
                                $isChecked  = in_array((int)$inf['id'], $selectedInfr, true);
                                $isComplice = in_array((int)$inf['id'], $selectedComp, true);
                                $catBadge   = match($inf['categorie']) {
                                    'criminelle'         => 'danger',
                                    'correctionnelle'    => 'warning',
                                    'contraventionnelle' => 'info',
                                    default              => 'secondary'
                                };
                            ?>
                            <div class="infraction-row p-2 mb-1 rounded <?= $isChecked ? 'infraction-checked border' : 'bg-white border' ?>"
                                 data-categorie="<?= htmlspecialchars($inf['categorie']) ?>"
                                 data-libelle="<?= htmlspecialchars(strtolower($inf['libelle'])) ?>"
                                 data-code="<?= htmlspecialchars(strtolower($inf['code'])) ?>"
                                 data-id="<?= $inf['id'] ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="form-check flex-grow-1 mb-0">
                                        <!-- name="infractions_unite[]" — correspond à PVController::update() -->
                                        <input class="form-check-input infraction-check" type="checkbox"
                                               name="infractions_unite[]" value="<?= $inf['id'] ?>"
                                               id="inf<?= $inf['id'] ?>"
                                               <?= $isChecked ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100" for="inf<?= $inf['id'] ?>" style="cursor:pointer;">
                                            <span class="badge bg-<?= $catBadge ?> me-1"><?= htmlspecialchars($inf['code']) ?></span>
                                            <span class="fw-semibold"><?= htmlspecialchars($inf['libelle']) ?></span>
                                            <span class="text-muted small ms-2">
                                                <?= ucfirst($inf['categorie']) ?>
                                                <?php if (!empty($inf['peine_min_mois']) || !empty($inf['peine_max_mois'])): ?>
                                                    · <?= (int)$inf['peine_min_mois'] ?>–<?= (int)$inf['peine_max_mois'] ?> mois
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-0 complicite-switch"
                                         style="<?= $isChecked ? '' : 'visibility:hidden;' ?>">
                                        <input class="form-check-input" type="checkbox"
                                               name="est_complicite[]" value="<?= $inf['id'] ?>"
                                               id="comp<?= $inf['id'] ?>"
                                               <?= $isComplice ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="comp<?= $inf['id'] ?>">
                                            Complicité
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div id="noInfractionMsg" class="text-center text-muted py-3" style="display:none;">
                                <i class="bi bi-search me-1"></i>Aucune infraction ne correspond à votre recherche.
                            </div>
                        </div>

                        <!-- Récap visuel -->
                        <div class="mt-3" id="selectedSummary" style="display:none;">
                            <div class="small text-muted mb-1">
                                <i class="bi bi-check2-circle text-success"></i>
                                <strong>Infractions sélectionnées :</strong>
                            </div>
                            <div id="selectedBadges" class="d-flex flex-wrap gap-1"></div>
                        </div>

                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle"></i>
                            Cochez toutes les infractions retenues. Activez le commutateur
                            <strong>« Complicité »</strong> à droite pour signaler qu'il s'agit
                            d'une complicité plutôt que d'une infraction principale.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Description des faits</label>
                <textarea name="description_faits" class="form-control" rows="4"><?= htmlspecialchars($_POST['description_faits'] ?? $pv['description_faits'] ?? '') ?></textarea>
            </div>
        </div></div>
    </div>

    <!-- ============================== -->
    <!-- Section antiterroriste         -->
    <!-- ============================== -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex align-items-center gap-2">
            <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input" id="chkAnti" name="est_antiterroriste" value="1"
                    <?= ($_POST['est_antiterroriste'] ?? $pv['est_antiterroriste']) ? 'checked' : '' ?>
                    onchange="toggleAnti(this.checked)">
                <label class="form-check-label fw-semibold text-danger" for="chkAnti">
                    🔴 Affaire antiterroriste
                </label>
            </div>
        </div>
        <div class="card-body" id="antiSection" style="<?= ($_POST['est_antiterroriste'] ?? $pv['est_antiterroriste']) ? '' : 'display:none' ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Région</label>
                    <?php $regSel = $_POST['region_id'] ?? $pv['region_id'] ?? ''; ?>
                    <select name="region_id" class="form-select" id="selRegion" onchange="loadDepts(this.value)">
                        <option value="">— Sélectionner —</option>
                        <?php foreach($regions as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $regSel == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Département</label>
                    <select name="departement_id" class="form-select" id="selDept" onchange="loadCommunes(this.value)">
                        <option value="">— Sélectionner —</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Commune</label>
                    <select name="commune_id" class="form-select" id="selCommune">
                        <option value="">— Sélectionner —</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Primo intervenants</label>
                    <div class="row g-2">
                        <?php
                        $primoSel = $_POST['primo_intervenants'] ?? $pv['primo_ids'] ?? [];
                        foreach($primos as $pi): ?>
                        <div class="col-md-4">
                            <div class="form-check border rounded p-2">
                                <input class="form-check-input" type="checkbox" name="primo_intervenants[]"
                                    value="<?= $pi['id'] ?>"
                                    id="pi<?= $pi['id'] ?>"
                                    <?= in_array($pi['id'], (array)$primoSel) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pi<?= $pi['id'] ?>"><?= htmlspecialchars($pi['nom']) ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-save me-1"></i>Enregistrer les modifications</button>
        <a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
</div></div>

<script>
/* ================================================================
   Antiterroriste : toggle + cascade Région → Département → Commune
================================================================ */
const PRELOAD_DEPT    = '<?= htmlspecialchars($_POST['departement_id'] ?? $pv['departement_id'] ?? '') ?>';
const PRELOAD_COMMUNE = '<?= htmlspecialchars($_POST['commune_id']     ?? $pv['commune_id']     ?? '') ?>';

function toggleAnti(v) {
    document.getElementById('antiSection').style.display = v ? '' : 'none';
}

function loadDepts(regionId) {
    if (!regionId) {
        document.getElementById('selDept').innerHTML = '<option value="">— Sélectionner —</option>';
        document.getElementById('selCommune').innerHTML = '<option value="">— Sélectionner —</option>';
        return;
    }
    fetch('<?= BASE_URL ?>/api/departements/' + regionId)
        .then(r => r.json())
        .then(d => {
            const sel = document.getElementById('selDept');
            sel.innerHTML = '<option value="">— Sélectionner —</option>';
            d.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.nom;
                if (String(item.id) === String(PRELOAD_DEPT)) opt.selected = true;
                sel.appendChild(opt);
            });
            if (PRELOAD_DEPT) loadCommunes(PRELOAD_DEPT);
        });
}

function loadCommunes(deptId) {
    if (!deptId) {
        document.getElementById('selCommune').innerHTML = '<option value="">— Sélectionner —</option>';
        return;
    }
    fetch('<?= BASE_URL ?>/api/communes/' + deptId)
        .then(r => r.json())
        .then(d => {
            const sel = document.getElementById('selCommune');
            sel.innerHTML = '<option value="">— Sélectionner —</option>';
            d.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.nom;
                if (String(item.id) === String(PRELOAD_COMMUNE)) opt.selected = true;
                sel.appendChild(opt);
            });
        });
}

// Pré-charger les départements/communes si une région est déjà sélectionnée
(function(){
    const reg = document.getElementById('selRegion').value;
    if (reg) loadDepts(reg);
})();

/* ================================================================
   GESTION DES INFRACTIONS MULTIPLES
   — Style navy + blanc identique à create.php
================================================================ */
(function(){
    const list         = document.getElementById('infractionsList');
    const search       = document.getElementById('infractionSearch');
    const btnClear     = document.getElementById('btnClearSearch');
    const counter      = document.getElementById('infractionsCount');
    const summary      = document.getElementById('selectedSummary');
    const badges       = document.getElementById('selectedBadges');
    const noMsg        = document.getElementById('noInfractionMsg');
    const filterRadios = document.querySelectorAll('input[name="filterCat"]');

    function updateUI() {
        const checks = list.querySelectorAll('.infraction-check');
        let n = 0;
        badges.innerHTML = '';

        checks.forEach(function(chk){
            const row      = chk.closest('.infraction-row');
            const switchEl = row.querySelector('.complicite-switch');
            const compChk  = row.querySelector('input[name="est_complicite[]"]');

            if (chk.checked) {
                n++;
                // Appliquer style navy (comme create.php)
                row.classList.add('infraction-checked', 'border-primary');
                row.classList.remove('bg-white');
                if (switchEl) switchEl.style.visibility = 'visible';

                const label     = row.querySelector('label.form-check-label');
                const codeBadge = label.querySelector('.badge');
                const text      = label.querySelector('.fw-semibold').textContent;
                const isCompl   = compChk && compChk.checked;

                const badge = document.createElement('span');
                badge.className = 'badge bg-secondary d-inline-flex align-items-center gap-1';
                badge.innerHTML = (isCompl ? '<i class="bi bi-link-45deg" title="Complicité"></i> ' : '') +
                    (codeBadge ? '<span class="me-1">' + codeBadge.textContent + '</span>' : '') + text;
                badges.appendChild(badge);
            } else {
                // Retirer style navy
                row.classList.remove('infraction-checked', 'border-primary');
                row.classList.add('bg-white');
                if (switchEl) switchEl.style.visibility = 'hidden';
                if (compChk) compChk.checked = false;
            }
        });

        counter.textContent = n;
        counter.className = 'badge ' + (n > 0 ? 'bg-light text-dark' : 'bg-light text-dark');
        summary.style.display = n > 0 ? 'block' : 'none';
    }

    function applyFilter() {
        const term = search.value.trim().toLowerCase();
        const cat  = document.querySelector('input[name="filterCat"]:checked').value;
        const rows = list.querySelectorAll('.infraction-row');
        let visible = 0;

        rows.forEach(function(row){
            const matchesText = !term ||
                row.dataset.libelle.includes(term) ||
                row.dataset.code.includes(term);

            let matchesCat = true;
            if (cat === 'selected') {
                matchesCat = row.querySelector('.infraction-check').checked;
            } else if (cat) {
                matchesCat = row.dataset.categorie === cat;
            }

            const show = matchesText && matchesCat;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        noMsg.style.display = visible === 0 ? 'block' : 'none';
    }

    list.addEventListener('change', function(e){
        if (e.target.classList.contains('infraction-check') ||
            e.target.name === 'est_complicite[]') {
            updateUI();
        }
    });

    search.addEventListener('input', applyFilter);
    btnClear.addEventListener('click', function(){
        search.value = '';
        applyFilter();
        search.focus();
    });

    filterRadios.forEach(function(r){
        r.addEventListener('change', applyFilter);
    });

    // Initialisation : appliquer les styles sur les infractions déjà cochées (pré-chargées)
    updateUI();
})();
</script>
