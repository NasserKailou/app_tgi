<?php
/**
 * Formulaire partagé de saisie/édition d'une mise en cause
 * Variables attendues : $mec (optionnel pour édition), $pvId, $formAction, $btnLabel
 */
$d = $mec ?? [];
$isEdit = !empty($d['id']);
?>
<form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data" novalidate>
  <?= CSRF::field() ?>

  <!-- ── Section Identité ─────────────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
      <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
        <i class="bi bi-person-exclamation me-1"></i>Identité de la mise en cause
      </span>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-warning text-dark"><i class="bi bi-person"></i></span>
            <input type="text" name="nom" class="form-control text-uppercase" required
                   placeholder="NOM DE FAMILLE"
                   value="<?= htmlspecialchars($d['nom'] ?? '') ?>">
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Prénom</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="prenom" class="form-control"
                   placeholder="Prénom(s)"
                   value="<?= htmlspecialchars($d['prenom'] ?? '') ?>">
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Alias / Surnom</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-incognito"></i></span>
            <input type="text" name="alias" class="form-control"
                   placeholder="Alias ou surnom"
                   value="<?= htmlspecialchars($d['alias'] ?? '') ?>">
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Nom de la mère</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-heart"></i></span>
            <input type="text" name="nom_mere" class="form-control"
                   placeholder="Prénom et nom de la mère"
                   value="<?= htmlspecialchars($d['nom_mere'] ?? '') ?>">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Sexe</label>
          <select name="sexe" class="form-select">
            <option value="M"       <?= ($d['sexe'] ?? 'M') === 'M'       ? 'selected' : '' ?>>Masculin</option>
            <option value="F"       <?= ($d['sexe'] ?? '') === 'F'         ? 'selected' : '' ?>>Féminin</option>
            <option value="Inconnu" <?= ($d['sexe'] ?? '') === 'Inconnu'   ? 'selected' : '' ?>>Inconnu</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Date de naissance</label>
          <input type="date" name="date_naissance" class="form-control"
                 value="<?= htmlspecialchars($d['date_naissance'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Lieu de naissance</label>
          <input type="text" name="lieu_naissance" class="form-control"
                 placeholder="Ville / Village"
                 value="<?= htmlspecialchars($d['lieu_naissance'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Nationalité</label>
          <input type="text" name="nationalite" class="form-control"
                 value="<?= htmlspecialchars($d['nationalite'] ?? 'Nigérienne') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Profession</label>
          <input type="text" name="profession" class="form-control"
                 placeholder="Profession"
                 value="<?= htmlspecialchars($d['profession'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Téléphone</label>
          <input type="tel" name="telephone" class="form-control"
                 placeholder="+227 XX XX XX XX"
                 value="<?= htmlspecialchars($d['telephone'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Adresse domicile</label>
          <input type="text" name="adresse" class="form-control"
                 placeholder="Quartier / Rue / Commune"
                 value="<?= htmlspecialchars($d['adresse'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Statut dans le dossier ──────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
      <i class="bi bi-tag me-2 text-warning"></i>Statut dans le dossier
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
          <select name="statut" class="form-select" id="statutMEC" required
                  onchange="toggleStatutAutre(this.value)">
            <option value="mise_en_cause" <?= ($d['statut'] ?? 'mise_en_cause') === 'mise_en_cause' ? 'selected' : '' ?>>Mise en cause</option>
            <option value="prevenu"       <?= ($d['statut'] ?? '') === 'prevenu'       ? 'selected' : '' ?>>Prévenu</option>
            <option value="temoin"        <?= ($d['statut'] ?? '') === 'temoin'        ? 'selected' : '' ?>>Témoin</option>
            <option value="autre"         <?= ($d['statut'] ?? '') === 'autre'         ? 'selected' : '' ?>>Autre (à préciser)</option>
          </select>
        </div>
        <div class="col-md-6" id="rowStatutAutre"
             style="display:<?= ($d['statut'] ?? '') === 'autre' ? 'block' : 'none' ?>">
          <label class="form-label fw-semibold">Préciser le statut</label>
          <input type="text" name="statut_autre_detail" class="form-control"
                 placeholder="Ex: Partie civile, Mis en examen..."
                 value="<?= htmlspecialchars($d['statut_autre_detail'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Antécédents judiciaires ─────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
      <i class="bi bi-clock-history me-2 text-danger"></i>Antécédents judiciaires
    </div>
    <div class="card-body">
      <div class="row g-3 align-items-center">
        <div class="col-md-4">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   name="est_connu_archives" id="knownArchives"
                   value="1"
                   <?= !empty($d['est_connu_archives']) ? 'checked' : '' ?>>
            <label class="form-check-label fw-semibold" for="knownArchives">
              Connu des archives (pas une première fois)
            </label>
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Nombre d'affaires précédentes</label>
          <input type="number" name="nb_affaires_precedentes" class="form-control"
                 min="0" value="<?= (int)($d['nb_affaires_precedentes'] ?? 0) ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Notes / Antécédents connus</label>
          <textarea name="notes_antecedents" class="form-control" rows="2"
                    placeholder="Résumé des antécédents judiciaires connus..."><?= htmlspecialchars($d['notes_antecedents'] ?? '') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Personne à contacter ───────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
      <i class="bi bi-telephone me-2 text-info"></i>Personne à contacter / Famille
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Nom de la personne à contacter</label>
          <input type="text" name="personne_contacter_nom" class="form-control"
                 placeholder="Nom Prénom"
                 value="<?= htmlspecialchars($d['personne_contacter_nom'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Téléphone</label>
          <input type="tel" name="personne_contacter_tel" class="form-control"
                 placeholder="+227..."
                 value="<?= htmlspecialchars($d['personne_contacter_tel'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Lien de parenté</label>
          <input type="text" name="personne_contacter_lien" class="form-control"
                 placeholder="Ex: Père, Mère, Époux(se), Tuteur..."
                 value="<?= htmlspecialchars($d['personne_contacter_lien'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Photo d'identité ───────────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
      <i class="bi bi-camera me-2 text-secondary"></i>Photo d'identité
    </div>
    <div class="card-body">
      <div class="row g-3 align-items-center">
        <?php if (!empty($d['photo'])): ?>
        <div class="col-auto">
          <img src="<?= BASE_URL ?>/<?= htmlspecialchars($d['photo']) ?>"
               alt="Photo" class="rounded border" style="height:80px;width:60px;object-fit:cover;">
        </div>
        <?php endif; ?>
        <div class="col">
          <label class="form-label fw-semibold">Photo <small class="text-muted">JPG/PNG ≤ 2 Mo</small></label>
          <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end mt-3">
    <a href="<?= BASE_URL ?>/pv/show/<?= htmlspecialchars($pvId ?? ($d['pv_id'] ?? '')) ?>#mises-en-cause"
       class="btn btn-outline-secondary">
      <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
      <i class="bi bi-person-exclamation me-2"></i><?= $btnLabel ?? 'Enregistrer' ?>
    </button>
  </div>
</form>

<script>
function toggleStatutAutre(val) {
    document.getElementById('rowStatutAutre').style.display = (val === 'autre') ? 'block' : 'none';
}
</script>
