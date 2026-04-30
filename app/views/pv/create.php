<?php $pageTitle = 'Nouveau PV'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv">PV</a></li>
        <li class="breadcrumb-item active">Nouveau PV</li>
    </ol></nav>
    <h4 class="fw-bold py-2 px-3 rounded text-white" style="background-color:#c0392b;">
        <i class="bi bi-file-plus me-2"></i>Enregistrer un nouveau PV
    </h4>
</div>

<form method="POST" action="<?= BASE_URL ?>/pv/store" novalidate>
    <?= CSRF::field() ?>
    <div class="row g-4">
        <!-- Colonne gauche -->
        <div class="col-lg-8">
            <!-- Identification -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-card-text me-2"></i>Identification du PV</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">N° de PV <span class="text-danger">*</span></label>
                            <input type="text" name="numero_pv" class="form-control" required value="<?= htmlspecialchars($_POST['numero_pv']??'') ?>" placeholder="ex: PV 456/2026/BCAN">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">N° d'ordre du PV <span class="text-muted small">(saisi manuellement)</span></label>
                            <input type="text" name="numero_ordre" class="form-control font-monospace" value="<?= htmlspecialchars($_POST['numero_ordre']??'') ?>" placeholder="ex: 001/2026">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                N° RP (Registre du Parquet)
                                <span class="text-muted small">(unique, saisi manuellement)</span>
                            </label>
                            <input type="text" name="numero_rp" class="form-control font-monospace text-primary fw-semibold"
                                   value="<?= htmlspecialchars($_POST['numero_rp']??'') ?>"
                                   placeholder="ex: RP N°001/2026/PARQUET">
                            <div class="form-text">Le système vérifie l'unicité du RP.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">N° RG (auto-généré)</label>
                            <input type="text" class="form-control bg-light font-monospace" value="<?= htmlspecialchars($suggestRG) ?>" readonly>
                            <div class="form-text">Attribué automatiquement à l'enregistrement</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date du PV <span class="text-danger">*</span></label>
                            <input type="date" name="date_pv" class="form-control" required value="<?= htmlspecialchars($_POST['date_pv']??date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de réception <span class="text-danger">*</span></label>
                            <input type="date" name="date_reception" class="form-control" required value="<?= htmlspecialchars($_POST['date_reception']??date('Y-m-d')) ?>">
                        </div>

                        <!-- 5 types d'affaire officiels -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-tag me-1 text-primary"></i>Type d'affaire — Pôle <span class="text-danger">*</span>
                            </label>
                            <select name="type_affaire" class="form-select" required>
                                <optgroup label="Droit Commun">
                                    <option value="droit_commun_mineur" <?= ($_POST['type_affaire']??'')==='droit_commun_mineur'?'selected':'' ?>>Droit Commun — Mineur</option>
                                    <option value="droit_commun_majeur" <?= ($_POST['type_affaire']??'droit_commun_majeur')==='droit_commun_majeur'?'selected':'' ?>>Droit Commun — Majeur</option>
                                </optgroup>
                                <optgroup label="Pôle Antiterroriste">
                                    <option value="pole_antiterro_mineur" <?= ($_POST['type_affaire']??'')==='pole_antiterro_mineur'?'selected':'' ?>>Pôle Antiterroriste — Mineur</option>
                                    <option value="pole_antiterro_majeur" <?= ($_POST['type_affaire']??'')==='pole_antiterro_majeur'?'selected':'' ?>>Pôle Antiterroriste — Majeur</option>
                                </optgroup>
                                <optgroup label="Pôle Économique">
                                    <option value="pole_economique" <?= ($_POST['type_affaire']??'')==='pole_economique'?'selected':'' ?>>Pôle Économique et Financier</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Unité d'enquête</label>
                            <select name="unite_enquete_id" class="form-select">
                                <option value="">— Sélectionner —</option>
                                <?php foreach ($unites as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($_POST['unite_enquete_id']??'')==$u['id']?'selected':'' ?>><?= htmlspecialchars($u['nom']) ?> (<?= $u['type'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- ===================================================== -->
                        <!-- INFRACTIONS MULTIPLES (saisie unité d'enquête)        -->
                        <!-- ===================================================== -->
                        <div class="col-12">
                            <div class="card border-primary border-2">
                                <div class="card-header bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
                                    <span class="fw-semibold text-primary">
                                        <i class="bi bi-list-check me-2"></i>Types d'infractions retenues
                                        <span class="text-muted small fw-normal">(plusieurs choix possibles)</span>
                                    </span>
                                    <span class="badge bg-primary" id="infractionsCount">0</span>
                                </div>
                                <div class="card-body">
                                    <!-- Recherche -->
                                    <div class="input-group input-group-sm mb-3">
                                        <span class="input-group-text bg-white">
                                            <i class="bi bi-search"></i>
                                        </span>
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

                                    <!-- Liste scrollable des infractions -->
                                    <div class="border rounded p-2" style="max-height: 320px; overflow-y: auto; background:#fafafa;" id="infractionsList">
                                        <?php
                                        $selectedInfr = (array)($_POST['infractions'] ?? []);
                                        $complicityArr = (array)($_POST['est_complicite'] ?? []);
                                        foreach ($infractions as $inf):
                                            $isChecked   = in_array($inf['id'], $selectedInfr);
                                            $isComplice  = in_array($inf['id'], $complicityArr);
                                            $catBadge    = match($inf['categorie']) {
                                                'criminelle'         => 'danger',
                                                'correctionnelle'    => 'warning',
                                                'contraventionnelle' => 'info',
                                                default              => 'secondary'
                                            };
                                        ?>
                                        <div class="infraction-row p-2 mb-1 rounded <?= $isChecked ? 'bg-primary bg-opacity-10 border border-primary' : 'bg-white border' ?>"
                                             data-categorie="<?= htmlspecialchars($inf['categorie']) ?>"
                                             data-libelle="<?= htmlspecialchars(strtolower($inf['libelle'])) ?>"
                                             data-code="<?= htmlspecialchars(strtolower($inf['code'])) ?>"
                                             data-id="<?= $inf['id'] ?>">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="form-check flex-grow-1 mb-0">
                                                    <input class="form-check-input infraction-check" type="checkbox"
                                                           name="infractions[]" value="<?= $inf['id'] ?>"
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
                                                <div class="form-check form-switch mb-0 complicite-switch" style="<?= $isChecked ? '' : 'visibility:hidden;' ?>">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="est_complicite[]" value="<?= $inf['id'] ?>"
                                                           id="comp<?= $inf['id'] ?>"
                                                           <?= $isComplice ? 'checked' : '' ?>>
                                                    <label class="form-check-label small text-muted" for="comp<?= $inf['id'] ?>">
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

                                    <!-- Récapitulatif des sélections -->
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
                        <!-- ===================================================== -->

                        <div class="col-12">
                            <label class="form-label">Description des faits</label>
                            <textarea name="description_faits" class="form-control" rows="4" placeholder="Décrire brièvement les faits..."><?= htmlspecialchars($_POST['description_faits']??'') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section antiterroriste -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex align-items-center justify-content-between fw-semibold">
                    <span><i class="bi bi-shield-exclamation me-2 text-danger"></i>Affaire antiterroriste</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="est_antiterroriste" id="switchAntiterro" value="1" <?= isset($_POST['est_antiterroriste'])?'checked':'' ?> onchange="toggleAntiterro()">
                        <label class="form-check-label" for="switchAntiterro">Cocher si affaire antiterroriste</label>
                    </div>
                </div>
                <div class="card-body" id="antiterroSection" style="display:<?= isset($_POST['est_antiterroriste'])?'block':'none' ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Région</label>
                            <select name="region_id" id="regionSelect" class="form-select" onchange="loadDepartements(this.value)">
                                <option value="">— Région —</option>
                                <?php foreach ($regions as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($_POST['region_id']??'')==$r['id']?'selected':'' ?>><?= htmlspecialchars($r['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Département</label>
                            <select name="departement_id" id="deptSelect" class="form-select" onchange="loadCommunes(this.value)">
                                <option value="">— Département —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Commune</label>
                            <select name="commune_id" id="communeSelect" class="form-select">
                                <option value="">— Commune —</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Primo intervenants</label>
                            <div class="row row-cols-2 row-cols-md-3 g-2">
                                <?php foreach ($primos as $pi): ?>
                                <div class="col">
                                    <div class="form-check border rounded p-2">
                                        <input class="form-check-input" type="checkbox" name="primo_intervenants[]" value="<?= $pi['id'] ?>" id="pi<?= $pi['id'] ?>" <?= in_array($pi['id'], (array)($_POST['primo_intervenants']??[]))?'checked':'' ?>>
                                        <label class="form-check-label small" for="pi<?= $pi['id'] ?>"><?= htmlspecialchars($pi['nom']) ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne droite -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Résumé</div>
                <div class="card-body">
                    <p class="text-muted small">Le numéro RG sera attribué automatiquement selon le format :<br><code>RG N°XXX/AAAA/TGI-NY</code></p>
                    <hr>
                    <p class="text-muted small mb-2"><strong>Infractions retenues :</strong></p>
                    <div id="sidebarInfractions" class="small">
                        <em class="text-muted">Aucune infraction sélectionnée</em>
                    </div>
                    <hr>
                    <p class="text-muted small mb-0">Une fois enregistré, le PV pourra être :<br>
                    • Affecté à un substitut<br>
                    • Classé sans suite<br>
                    • Transféré en instruction ou directement en audience</p>
                </div>
                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-2"></i>Enregistrer le PV</button>
                    <a href="<?= BASE_URL ?>/pv" class="btn btn-outline-secondary w-100 mt-2">Annuler</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
/* ================================================================
   Cascade Région → Département → Commune
================================================================ */
var GEO_DATA = <?= $geoJson ?? '{}' ?>;

function toggleAntiterro(){
    document.getElementById('antiterroSection').style.display =
        document.getElementById('switchAntiterro').checked ? 'block' : 'none';
}

function loadDepartements(regionId) {
    var deptSel = document.getElementById('deptSelect');
    var commSel = document.getElementById('communeSelect');
    deptSel.innerHTML = '<option value="">— Département —</option>';
    commSel.innerHTML = '<option value="">— Commune —</option>';
    if (!regionId || !GEO_DATA[regionId]) return;
    GEO_DATA[regionId].departements.forEach(function(d) {
        var opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.nom;
        deptSel.appendChild(opt);
    });
    var savedDept = '<?= htmlspecialchars($_POST['departement_id'] ?? '') ?>';
    if (savedDept) { deptSel.value = savedDept; loadCommunes(savedDept); }
}

function loadCommunes(deptId) {
    var commSel = document.getElementById('communeSelect');
    commSel.innerHTML = '<option value="">— Commune —</option>';
    if (!deptId) return;
    var regionId = document.getElementById('regionSelect').value;
    if (!regionId || !GEO_DATA[regionId]) return;
    var dept = GEO_DATA[regionId].departements.find(function(d){ return String(d.id) === String(deptId); });
    if (!dept) return;
    dept.communes.forEach(function(c) {
        var opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.nom;
        commSel.appendChild(opt);
    });
    var savedComm = '<?= htmlspecialchars($_POST['commune_id'] ?? '') ?>';
    if (savedComm) commSel.value = savedComm;
}

(function(){
    var savedRegion = '<?= htmlspecialchars($_POST['region_id'] ?? '') ?>';
    if (savedRegion) {
        document.getElementById('regionSelect').value = savedRegion;
        loadDepartements(savedRegion);
    }
})();

/* ================================================================
   GESTION DES INFRACTIONS MULTIPLES
================================================================ */
(function(){
    const list           = document.getElementById('infractionsList');
    const search         = document.getElementById('infractionSearch');
    const btnClear       = document.getElementById('btnClearSearch');
    const counter        = document.getElementById('infractionsCount');
    const summary        = document.getElementById('selectedSummary');
    const badges         = document.getElementById('selectedBadges');
    const sidebar        = document.getElementById('sidebarInfractions');
    const noMsg          = document.getElementById('noInfractionMsg');
    const filterRadios   = document.querySelectorAll('input[name="filterCat"]');

    function updateUI() {
        const checks = list.querySelectorAll('.infraction-check');
        let n = 0;
        badges.innerHTML = '';
        const sidebarItems = [];

        checks.forEach(function(chk){
            const row = chk.closest('.infraction-row');
            const switchEl = row.querySelector('.complicite-switch');
            const compChk = row.querySelector('input[name="est_complicite[]"]');

            if (chk.checked) {
                n++;
                row.classList.remove('bg-white');
                row.classList.add('bg-primary', 'bg-opacity-10', 'border-primary');
                if (switchEl) switchEl.style.visibility = 'visible';

                // Récupérer libellé et catégorie depuis le DOM
                const label = row.querySelector('label.form-check-label');
                const codeBadge = label.querySelector('.badge');
                const text = label.querySelector('.fw-semibold').textContent;
                const isCompl = compChk && compChk.checked;

                // Badge dans le récap
                const badge = document.createElement('span');
                badge.className = 'badge ' + (codeBadge ? codeBadge.className.replace('me-1','') : 'bg-secondary') + ' d-inline-flex align-items-center gap-1';
                badge.innerHTML = (isCompl ? '<i class="bi bi-link-45deg" title="Complicité"></i> ' : '') + text;
                badges.appendChild(badge);

                // Item sidebar
                sidebarItems.push(
                    '<div class="mb-1"><i class="bi bi-check2 text-success"></i> ' +
                    text +
                    (isCompl ? ' <span class="badge bg-secondary ms-1">complicité</span>' : '') +
                    '</div>'
                );
            } else {
                row.classList.remove('bg-primary','bg-opacity-10','border-primary');
                row.classList.add('bg-white');
                if (switchEl) switchEl.style.visibility = 'hidden';
                if (compChk) compChk.checked = false;
            }
        });

        counter.textContent = n;
        counter.className = 'badge ' + (n > 0 ? 'bg-success' : 'bg-secondary');
        summary.style.display = n > 0 ? 'block' : 'none';
        sidebar.innerHTML = n > 0
            ? sidebarItems.join('')
            : '<em class="text-muted">Aucune infraction sélectionnée</em>';
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

    // Événements
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

    // Init
    updateUI();
})();
</script>
