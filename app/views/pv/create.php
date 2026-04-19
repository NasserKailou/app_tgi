<?php $pageTitle = 'Nouveau PV'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv">PV</a></li>
        <li class="breadcrumb-item active">Nouveau PV</li>
    </ol></nav>
    <h4 class="fw-bold"><i class="bi bi-file-plus me-2 text-primary"></i>Enregistrer un nouveau PV</h4>
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
                            <label class="form-label">Type d'infraction</label>
                            <select name="infraction_id" class="form-select">
                                <option value="">— Sélectionner (optionnel) —</option>
                                <?php foreach ($infractions as $inf): ?>
                                <option value="<?= $inf['id'] ?>" <?= ($_POST['infraction_id']??'')==$inf['id']?'selected':'' ?>>
                                    <?= htmlspecialchars($inf['libelle']) ?> (<?= ucfirst($inf['categorie']) ?>)
                                </option>
                                <?php endforeach; ?>
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
function toggleAntiterro(){
    document.getElementById('antiterroSection').style.display=document.getElementById('switchAntiterro').checked?'block':'none';
}
function loadDepartements(regionId){
    if(!regionId){document.getElementById('deptSelect').innerHTML='<option value="">— Département —</option>';document.getElementById('communeSelect').innerHTML='<option value="">— Commune —</option>';return;}
    fetch(`<?= BASE_URL ?>/api/departements/${regionId}`).then(r=>r.json()).then(data=>{
        let html='<option value="">— Département —</option>';
        data.forEach(d=>html+=`<option value="${d.id}">${d.nom}</option>`);
        document.getElementById('deptSelect').innerHTML=html;
        document.getElementById('communeSelect').innerHTML='<option value="">— Commune —</option>';
    });
}
function loadCommunes(deptId){
    if(!deptId){document.getElementById('communeSelect').innerHTML='<option value="">— Commune —</option>';return;}
    fetch(`<?= BASE_URL ?>/api/communes/${deptId}`).then(r=>r.json()).then(data=>{
        let html='<option value="">— Commune —</option>';
        data.forEach(c=>html+=`<option value="${c.id}">${c.nom}</option>`);
        document.getElementById('communeSelect').innerHTML=html;
    });
}
</script>
