<?php /* app/views/mandats/create.php */ ?>
<?php $pageTitle = 'Émettre un mandat'; ?>

<div class="d-flex align-items-center mb-4 gap-3">
    <a href="<?= BASE_URL ?>/mandats" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h3 mb-0 fw-bold">
        <i class="bi bi-file-ruled text-danger me-2"></i>Émettre un mandat de justice
    </h1>
</div>

<form method="post" action="<?= BASE_URL ?>/mandats/store" id="formMandat">
<?= CSRF::field() ?>

<div class="row g-4">
    <!-- ═══════════════ Colonne gauche ═══════════════ -->
    <div class="col-lg-7">

        <!-- Carte 1 : Informations du mandat -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white fw-semibold">
                <i class="bi bi-file-ruled me-2"></i>Informations du mandat
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Type de mandat <span class="text-danger">*</span>
                        </label>
                        <select name="type_mandat" id="selTypeMandat" class="form-select" required>
                            <option value="">— Choisir —</option>
                            <option value="arret">🔴 Mandat d'arrêt</option>
                            <option value="depot">⚫ Mandat de dépôt</option>
                            <option value="amener">🟡 Mandat d'amener</option>
                            <option value="comparution">🔵 Mandat de comparution</option>
                            <option value="perquisition">🟣 Mandat de perquisition</option>
                            <option value="liberation">🟢 Mandat de libération</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Dossier RG associé</label>
                        <select name="dossier_id" class="form-select" id="selDossier">
                            <option value="">— Sans dossier existant —</option>
                            <?php foreach ($dossiers as $d): ?>
                                <option value="<?= $d['id'] ?>">
                                    <?= htmlspecialchars($d['numero_rg']) ?> —
                                    <?= htmlspecialchars(substr($d['objet'], 0, 50)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Un nouveau dossier sera créé si aucun n'est sélectionné</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Date d'émission <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_emission" class="form-control"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date d'expiration</label>
                        <input type="date" name="date_expiration" class="form-control">
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle"></i> Vide = pas d'expiration
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date d'exhibition au prévenu</label>
                        <input type="date" name="date_exhibe" class="form-control">
                        <div class="form-text">À renseigner si déjà notifié</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Motif du mandat <span class="text-danger">*</span>
                        </label>
                        <textarea name="motif" class="form-control" rows="4" required
                                  placeholder="Exposez les faits et le motif justifiant l'émission du mandat…"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Infraction(s) retenue(s)</label>
                        <input type="text" name="infraction_libelle" class="form-control"
                               placeholder="Ex : Actes de terrorisme, art. 421-1 CP…">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lieu d'exécution prévu</label>
                        <input type="text" name="lieu_execution" class="form-control"
                               placeholder="Ex : Maison d'Arrêt de Niamey, domicile…">
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="flagrant_delit" value="1"
                                   class="form-check-input" id="cbFlagrant">
                            <label class="form-check-label fw-semibold" for="cbFlagrant">
                                <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i>
                                En cas de <strong>flagrant délit</strong>
                            </label>
                            <div class="form-text">
                                Coché automatiquement pour les mandats de dépôt (modèle officiel).
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- ═══════════════ Colonne droite — Cible ═══════════════ -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-person-fill me-2"></i>Personne ciblée par le mandat
            </div>
            <div class="card-body">

                <!-- Onglets de sélection -->
                <ul class="nav nav-pills nav-fill mb-3" id="cibleTabs">
                    <li class="nav-item">
                        <button type="button" class="nav-link active btn-sm"
                                data-tab="nouveau" onclick="switchTab('nouveau', event)">
                            <i class="bi bi-person-plus me-1"></i>Nouvelle personne
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btn-sm"
                                data-tab="detenu" onclick="switchTab('detenu', event)">
                            <i class="bi bi-person-lock me-1"></i>Détenu existant
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btn-sm"
                                data-tab="partie" onclick="switchTab('partie', event)">
                            <i class="bi bi-people me-1"></i>Partie au dossier
                        </button>
                    </li>
                </ul>

                <!-- ─── Détenu existant ─── -->
                <div id="tab-detenu" class="cible-tab d-none">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rechercher un détenu</label>
                        <input type="text" id="searchDetenu" class="form-control"
                               placeholder="Nom, prénom ou N° écrou…">
                        <div id="resultsDetenu" class="list-group mt-1"></div>
                    </div>
                    <input type="hidden" name="detenu_id" id="detenuId">
                    <div id="detenuSelected" class="alert alert-success d-none py-2 small"></div>
                </div>

                <!-- ─── Partie existante ─── -->
                <div id="tab-partie" class="cible-tab d-none">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rechercher une partie</label>
                        <input type="text" id="searchPartie" class="form-control"
                               placeholder="Nom, prénom…">
                        <div id="resultsPartie" class="list-group mt-1"></div>
                    </div>
                    <input type="hidden" name="partie_id" id="partieId">
                    <div id="partieSelected" class="alert alert-success d-none py-2 small"></div>
                </div>

                <!-- ─── Nouvelle personne ─── -->
                <div id="tab-nouveau" class="cible-tab">
                    <div class="row g-2">

                        <!-- État civil principal -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nouveau_nom" id="nvNom"
                                   class="form-control text-uppercase" placeholder="NOM">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prénom(s)</label>
                            <input type="text" name="nouveau_prenom" class="form-control"
                                   placeholder="Prénom(s)">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sexe</label>
                            <select name="sexe" class="form-select">
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date de naissance</label>
                            <input type="date" name="nouveau_ddn" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lieu de naissance</label>
                            <input type="text" name="nouveau_lieu_naissance" class="form-control"
                                   placeholder="Ville, pays">
                        </div>

                        <!-- Filiation -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom du père</label>
                            <input type="text" name="nouveau_pere" class="form-control"
                                   placeholder="Nom et prénom du père">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom de la mère</label>
                            <input type="text" name="nouveau_mere" class="form-control"
                                   placeholder="Nom et prénom de la mère">
                        </div>

                        <!-- Identité civile -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nationalité</label>
                            <input type="text" name="nouveau_nationalite" class="form-control"
                                   value="Nigérienne">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Profession</label>
                            <input type="text" name="nouveau_profession" class="form-control"
                                   placeholder="Profession exercée">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Adresse / Domicile</label>
                            <input type="text" name="nouveau_adresse" class="form-control"
                                   placeholder="Adresse connue">
                        </div>

                        <!-- Renseignements complémentaires -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Situation de famille</label>
                            <input type="text" name="situation_famille" class="form-control"
                                   placeholder="Célibataire, marié(e), …">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service militaire</label>
                            <input type="text" name="service_militaire" class="form-control"
                                   placeholder="Effectué / Dispensé / Néant">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Condamnations antérieures</label>
                            <input type="text" name="condamnations" class="form-control"
                                   placeholder="Néant ou détails">
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Boutons -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="bi bi-file-ruled me-2"></i>Émettre le mandat
            </button>
            <a href="<?= BASE_URL ?>/mandats" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</div>

</form>

<script>
var BASE_URL = '<?= BASE_URL ?>';

/* ═══════════════ Onglets de sélection de la cible ═══════════════ */
function switchTab(tab, e) {
    if (e) e.preventDefault();
    document.querySelectorAll('.cible-tab').forEach(el => el.classList.add('d-none'));
    document.getElementById('tab-' + tab).classList.remove('d-none');
    document.querySelectorAll('#cibleTabs .nav-link').forEach(el => {
        el.classList.toggle('active', el.dataset.tab === tab);
    });
    if (tab !== 'detenu') {
        document.getElementById('detenuId').value = '';
        document.getElementById('detenuSelected').classList.add('d-none');
    }
    if (tab !== 'partie') {
        document.getElementById('partieId').value = '';
        document.getElementById('partieSelected').classList.add('d-none');
    }
}

/* ═══════════════ Cochage automatique flagrant délit pour mandat de dépôt ═══════════════ */
document.getElementById('selTypeMandat').addEventListener('change', function () {
    const cb = document.getElementById('cbFlagrant');
    if (this.value === 'depot') {
        cb.checked = true;
    } else {
        cb.checked = false;
    }
});

/* ═══════════════ Recherche détenu (AJAX) ═══════════════ */
let debounceDetenu;
document.getElementById('searchDetenu').addEventListener('input', function () {
    clearTimeout(debounceDetenu);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('resultsDetenu').innerHTML = ''; return; }
    debounceDetenu = setTimeout(() => {
        fetch(BASE_URL + '/api/mandat-person-search?q=' + encodeURIComponent(q) + '&type=detenu',
              { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById('resultsDetenu');
                el.innerHTML = data.map(d =>
                    `<button type="button" class="list-group-item list-group-item-action py-2 small"
                            onclick="selectDetenu(${d.id}, '${d.label.replace(/'/g, "\\'")}')">
                        <i class="bi bi-person-lock me-1 text-danger"></i>${d.label}
                        <span class="badge bg-secondary ms-1">${d.statut || ''}</span>
                     </button>`
                ).join('') || '<div class="list-group-item text-muted small">Aucun résultat</div>';
            })
            .catch(() => {
                document.getElementById('resultsDetenu').innerHTML =
                    '<div class="list-group-item text-danger small">Erreur de recherche</div>';
            });
    }, 300);
});

function selectDetenu(id, label) {
    document.getElementById('detenuId').value = id;
    document.getElementById('detenuSelected').textContent = '✓ Sélectionné : ' + label;
    document.getElementById('detenuSelected').classList.remove('d-none');
    document.getElementById('resultsDetenu').innerHTML = '';
    document.getElementById('searchDetenu').value = label;
}

/* ═══════════════ Recherche partie (AJAX) ═══════════════ */
let debouncePartie;
document.getElementById('searchPartie').addEventListener('input', function () {
    clearTimeout(debouncePartie);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('resultsPartie').innerHTML = ''; return; }
    debouncePartie = setTimeout(() => {
        fetch(BASE_URL + '/api/mandat-person-search?q=' + encodeURIComponent(q) + '&type=partie',
              { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById('resultsPartie');
                el.innerHTML = data.map(d =>
                    `<button type="button" class="list-group-item list-group-item-action py-2 small"
                            onclick="selectPartie(${d.id}, '${d.label.replace(/'/g, "\\'")}')">
                        <i class="bi bi-person me-1 text-primary"></i>${d.label}
                     </button>`
                ).join('') || '<div class="list-group-item text-muted small">Aucun résultat</div>';
            })
            .catch(() => {
                document.getElementById('resultsPartie').innerHTML =
                    '<div class="list-group-item text-danger small">Erreur de recherche</div>';
            });
    }, 300);
});

function selectPartie(id, label) {
    document.getElementById('partieId').value = id;
    document.getElementById('partieSelected').textContent = '✓ Sélectionné : ' + label;
    document.getElementById('partieSelected').classList.remove('d-none');
    document.getElementById('resultsPartie').innerHTML = '';
    document.getElementById('searchPartie').value = label;
}

/* ═══════════════ Validation finale ═══════════════ */
document.getElementById('formMandat').addEventListener('submit', function (e) {
    const tab = document.querySelector('#cibleTabs .nav-link.active').dataset.tab;

    if (tab === 'nouveau') {
        const nom = document.getElementById('nvNom').value.trim();
        if (!nom) {
            e.preventDefault();
            alert('Veuillez saisir au minimum le nom de la personne ciblée.');
            return;
        }
    }
    if (tab === 'detenu' && !document.getElementById('detenuId').value) {
        e.preventDefault();
        alert('Veuillez sélectionner un détenu dans la liste.');
        return;
    }
    if (tab === 'partie' && !document.getElementById('partieId').value) {
        e.preventDefault();
        alert('Veuillez sélectionner une partie dans la liste.');
        return;
    }
});
</script>
