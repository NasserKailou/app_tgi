<?php /* app/views/mandats/edit.php */ ?>
<?php $pageTitle = 'Modifier le mandat ' . $mandat['numero']; ?>

<div class="d-flex align-items-center mb-4 gap-3">
    <a href="<?= BASE_URL ?>/mandats/show/<?= $mandat['id'] ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h3 mb-0 fw-bold">
        <i class="bi bi-pencil-square text-warning me-2"></i>
        Modifier le mandat <code><?= htmlspecialchars($mandat['numero']) ?></code>
    </h1>
</div>

<?php if (!empty($flash['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($flash['error']) ?></div>
<?php endif; ?>

<form method="post" action="<?= BASE_URL ?>/mandats/update/<?= $mandat['id'] ?>" id="formMandat">
<?= CSRF::field() ?>

<div class="row g-4">
    <!-- ═══════════════ Colonne gauche ═══════════════ -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning fw-semibold">
                <i class="bi bi-file-ruled me-2"></i>Informations du mandat
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Type de mandat <span class="text-danger">*</span></label>
                        <select name="type_mandat" id="selTypeMandat" class="form-select" required>
                            <option value="">— Choisir —</option>
                            <?php
                            $types = [
                                'arret'        => "🔴 Mandat d'arrêt",
                                'depot'        => "⚫ Mandat de dépôt",
                                'amener'       => "🟡 Mandat d'amener",
                                'comparution'  => "🔵 Mandat de comparution",
                                'perquisition' => "🟣 Mandat de perquisition",
                                'liberation'   => "🟢 Mandat de libération",
                            ];
                            foreach ($types as $k => $lbl):
                                $sel = $mandat['type_mandat'] === $k ? 'selected' : '';
                            ?>
                                <option value="<?= $k ?>" <?= $sel ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Dossier RG associé</label>
                        <select name="dossier_id" class="form-select">
                            <option value="">— Sans dossier —</option>
                            <?php foreach ($dossiers as $d): ?>
                                <option value="<?= $d['id'] ?>"
                                        <?= (int)$mandat['dossier_id'] === (int)$d['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['numero_rg']) ?> —
                                    <?= htmlspecialchars(substr($d['objet'], 0, 50)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date d'émission <span class="text-danger">*</span></label>
                        <input type="date" name="date_emission" class="form-control" required
                               value="<?= htmlspecialchars($mandat['date_emission'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date d'expiration</label>
                        <input type="date" name="date_expiration" class="form-control"
                               value="<?= htmlspecialchars($mandat['date_expiration'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date d'exhibition</label>
                        <input type="date" name="date_exhibe" class="form-control"
                               value="<?= htmlspecialchars($mandat['date_exhibe'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Motif <span class="text-danger">*</span></label>
                        <textarea name="motif" class="form-control" rows="4" required><?= htmlspecialchars($mandat['motif'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Infraction(s) retenue(s)</label>
                        <input type="text" name="infraction_libelle" class="form-control"
                               value="<?= htmlspecialchars($mandat['infraction_libelle'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lieu d'exécution</label>
                        <input type="text" name="lieu_execution" class="form-control"
                               value="<?= htmlspecialchars($mandat['lieu_execution'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="flagrant_delit" value="1" class="form-check-input" id="cbFlagrant"
                                   <?= !empty($mandat['flagrant_delit']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="cbFlagrant">
                                <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i>
                                En cas de <strong>flagrant délit</strong>
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════ Colonne droite ═══════════════ -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-person-fill me-2"></i>Personne ciblée
            </div>
            <div class="card-body">
                <?php if (!empty($mandat['detenu_id'])): ?>
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-person-lock me-1"></i>
                        Détenu existant lié (id = <?= (int)$mandat['detenu_id'] ?>).
                        <input type="hidden" name="detenu_id" value="<?= (int)$mandat['detenu_id'] ?>">
                    </div>
                <?php elseif (!empty($mandat['partie_id'])): ?>
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-people me-1"></i>
                        Partie au dossier liée (id = <?= (int)$mandat['partie_id'] ?>).
                        <input type="hidden" name="partie_id" value="<?= (int)$mandat['partie_id'] ?>">
                    </div>
                <?php endif; ?>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nouveau_nom" class="form-control text-uppercase"
                               value="<?= htmlspecialchars($mandat['nouveau_nom'] ?? '') ?>" id="nvNom">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Prénom(s)</label>
                        <input type="text" name="nouveau_prenom" class="form-control"
                               value="<?= htmlspecialchars($mandat['nouveau_prenom'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sexe</label>
                        <select name="sexe" class="form-select">
                            <option value="M" <?= ($mandat['sexe'] ?? '') === 'M' ? 'selected' : '' ?>>Masculin</option>
                            <option value="F" <?= ($mandat['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Féminin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date de naissance</label>
                        <input type="date" name="nouveau_ddn" class="form-control"
                               value="<?= htmlspecialchars($mandat['nouveau_ddn'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Lieu de naissance</label>
                        <input type="text" name="nouveau_lieu_naissance" class="form-control"
                               value="<?= htmlspecialchars($mandat['nouveau_lieu_naissance'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nom du père</label>
                        <input type="text" name="nouveau_pere" class="form-control"
                               value="<?= htmlspecialchars($mandat['nouveau_pere'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nom de la mère</label>
                        <input type="text" name="nouveau_mere" class="form-control"
                               value="<?= htmlspecialchars($mandat['nouveau_mere'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nationalité</label>
                        <input type="text" name="nouveau_nationalite" class="form-control"
                               value="<?= htmlspecialchars($mandat['nouveau_nationalite'] ?? 'Nigérienne') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Profession</label>
                        <input type="text" name="nouveau_profession" class="form-control"
                               value="<?= htmlspecialchars($mandat['nouveau_profession'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Adresse / Domicile</label>
                        <input type="text" name="nouveau_adresse" class="form-control"
                               value="<?= htmlspecialchars($mandat['nouveau_adresse'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Situation de famille</label>
                        <input type="text" name="situation_famille" class="form-control"
                               value="<?= htmlspecialchars($mandat['situation_famille'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Service militaire</label>
                        <input type="text" name="service_militaire" class="form-control"
                               value="<?= htmlspecialchars($mandat['service_militaire'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Condamnations antérieures</label>
                        <input type="text" name="condamnations" class="form-control"
                               value="<?= htmlspecialchars($mandat['condamnations'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-warning btn-lg">
                <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications
            </button>
            <a href="<?= BASE_URL ?>/mandats/show/<?= $mandat['id'] ?>" class="btn btn-outline-secondary">
                Annuler
            </a>

            <?php
            $user = Auth::currentUser();
            $roleCode = $user['role_code'] ?? '';
            $isAdmin = in_array($roleCode, ['admin','procureur'], true);
            ?>
            <?php if ($isAdmin && ($mandat['statut'] ?? '') !== 'execute'): ?>
                <hr>
                <form method="post" action="<?= BASE_URL ?>/mandats/delete/<?= $mandat['id'] ?>"
                      onsubmit="return confirm('⚠ Supprimer définitivement ce mandat ?\nCette action est irréversible.');">
                    <?= CSRF::field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-2"></i>Supprimer définitivement
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

</form>

<script>
// Cocher automatiquement flagrant délit si dépôt
document.getElementById('selTypeMandat').addEventListener('change', function () {
    if (this.value === 'depot') document.getElementById('cbFlagrant').checked = true;
});

// Validation : nom obligatoire seulement si pas de détenu/partie liés
document.getElementById('formMandat').addEventListener('submit', function (e) {
    var hasDetenu = document.querySelector('input[name="detenu_id"]');
    var hasPartie = document.querySelector('input[name="partie_id"]');
    if (!hasDetenu && !hasPartie) {
        var nom = document.getElementById('nvNom').value.trim();
        if (!nom) {
            e.preventDefault();
            alert('Veuillez saisir le nom de la personne ciblée.');
        }
    }
});
</script>
