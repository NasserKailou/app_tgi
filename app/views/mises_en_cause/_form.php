<?php
/**
 * Formulaire partagé de saisie/édition d'une mise en cause
 * Variables attendues : $mec (optionnel pour édition), $pvId, $formAction, $btnLabel
 * Variables optionnelles : $infractions, $mecInfractions
 * Utilisé en standalone (edit.php) ET embarqué dans un modal (pv/show.php)
 */
$d = $mec ?? [];
$isEdit   = !empty($d['id']);
$_inModal = isset($inModal) ? (bool)$inModal : false;
$userRole = Auth::roleCode();
$isSubstitut = in_array($userRole, ['substitut_procureur','procureur','admin']);

// Infractions existantes pour ce MEC (depuis contexte ou DB)
$mecInfrUnite     = array_column($mecInfractions['unite']     ?? [], 'id');
$mecInfrSubstitut = array_column($mecInfractions['substitut'] ?? [], 'id');
?>
<form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data" novalidate>
  <?= CSRF::field() ?>

  <!-- ── Section Identité ─────────────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-warning text-dark fw-semibold py-2">
      <i class="bi bi-person-exclamation me-2"></i>Identité de la mise en cause
    </div>
    <div class="card-body py-3">
      <div class="row g-2">
        <div class="col-sm-4">
          <label class="form-label fw-semibold small">Nom <span class="text-danger">*</span></label>
          <input type="text" name="nom" class="form-control form-control-sm text-uppercase" required
                 placeholder="NOM DE FAMILLE" value="<?= htmlspecialchars($d['nom'] ?? '') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold small">Prénom</label>
          <input type="text" name="prenom" class="form-control form-control-sm"
                 placeholder="Prénom(s)" value="<?= htmlspecialchars($d['prenom'] ?? '') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold small">Alias / Surnom</label>
          <input type="text" name="alias" class="form-control form-control-sm"
                 placeholder="Alias" value="<?= htmlspecialchars($d['alias'] ?? '') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold small">Nom de la mère</label>
          <input type="text" name="nom_mere" class="form-control form-control-sm"
                 placeholder="Prénom et nom de la mère" value="<?= htmlspecialchars($d['nom_mere'] ?? '') ?>">
        </div>
        <div class="col-sm-2">
          <label class="form-label fw-semibold small">Sexe</label>
          <select name="sexe" class="form-select form-select-sm">
            <option value="M"       <?= ($d['sexe'] ?? 'M') === 'M'       ? 'selected' : '' ?>>Masculin</option>
            <option value="F"       <?= ($d['sexe'] ?? '') === 'F'         ? 'selected' : '' ?>>Féminin</option>
            <option value="Inconnu" <?= ($d['sexe'] ?? '') === 'Inconnu'   ? 'selected' : '' ?>>Inconnu</option>
          </select>
        </div>
        <div class="col-sm-3">
          <label class="form-label fw-semibold small">Date de naissance</label>
          <input type="date" name="date_naissance" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($d['date_naissance'] ?? '') ?>">
        </div>
        <div class="col-sm-3">
          <label class="form-label fw-semibold small">Lieu de naissance</label>
          <input type="text" name="lieu_naissance" class="form-control form-control-sm"
                 placeholder="Ville / Village" value="<?= htmlspecialchars($d['lieu_naissance'] ?? '') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold small">Nationalité</label>
          <input type="text" name="nationalite" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($d['nationalite'] ?? 'Nigérienne') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold small">Profession</label>
          <input type="text" name="profession" class="form-control form-control-sm"
                 placeholder="Profession" value="<?= htmlspecialchars($d['profession'] ?? '') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold small">Téléphone</label>
          <input type="tel" name="telephone" class="form-control form-control-sm"
                 placeholder="+227 XX XX XX XX" value="<?= htmlspecialchars($d['telephone'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold small">Adresse domicile</label>
          <input type="text" name="adresse" class="form-control form-control-sm"
                 placeholder="Quartier / Rue / Commune" value="<?= htmlspecialchars($d['adresse'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Statut dans le dossier ──────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold py-2">
      <i class="bi bi-tag me-2 text-warning"></i>Statut judiciaire
    </div>
    <div class="card-body py-3">
      <div class="row g-2">
        <div class="col-sm-6">
          <label class="form-label fw-semibold small">Statut <span class="text-danger">*</span></label>
          <select name="statut" class="form-select form-select-sm" id="statutMEC" required
                  onchange="toggleStatutAutre(this.value)">
            <option value="mise_en_cause" <?= ($d['statut'] ?? 'mise_en_cause') === 'mise_en_cause' ? 'selected' : '' ?>>Mise en cause</option>
            <option value="prevenu"       <?= ($d['statut'] ?? '') === 'prevenu'       ? 'selected' : '' ?>>Prévenu</option>
            <option value="temoin"        <?= ($d['statut'] ?? '') === 'temoin'        ? 'selected' : '' ?>>Témoin</option>
            <option value="autre"         <?= ($d['statut'] ?? '') === 'autre'         ? 'selected' : '' ?>>Autre (à préciser)</option>
          </select>
        </div>
        <div class="col-sm-6" id="rowStatutAutre"
             style="display:<?= ($d['statut'] ?? '') === 'autre' ? 'block' : 'none' ?>">
          <label class="form-label fw-semibold small">Préciser le statut</label>
          <input type="text" name="statut_autre_detail" class="form-control form-control-sm"
                 placeholder="Ex: Partie civile, Mis en examen..."
                 value="<?= htmlspecialchars($d['statut_autre_detail'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Infractions retenues (Unité d'enquête) ─────────────────────── -->
  <?php if (!empty($infractions)): ?>
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-light fw-semibold py-2">
      <i class="bi bi-gavel me-2 text-primary"></i>Infractions retenues
      <span class="badge bg-primary ms-2 small">Unité d'enquête</span>
    </div>
    <div class="card-body py-2">
      <?php
      $catColors = ['criminelle'=>'danger','correctionnelle'=>'warning text-dark','contraventionnelle'=>'secondary'];
      $grouped   = [];
      foreach ($infractions as $inf) { $grouped[$inf['categorie']][] = $inf; }
      foreach ($grouped as $cat => $infs): ?>
      <div class="mb-2">
        <div class="fw-semibold small mb-1">
          <span class="badge bg-<?= $catColors[$cat] ?? 'secondary' ?>"><?= ucfirst($cat) ?></span>
        </div>
        <div class="row g-1">
          <?php foreach ($infs as $inf): ?>
          <div class="col-sm-6 col-lg-4">
            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox"
                     name="infractions_mec[]" value="<?= $inf['id'] ?>"
                     id="meci_u_<?= $inf['id'] ?>"
                     <?= in_array((int)$inf['id'], $mecInfrUnite) ? 'checked' : '' ?>>
              <label class="form-check-label small" for="meci_u_<?= $inf['id'] ?>">
                <span class="text-primary fw-semibold"><?= htmlspecialchars($inf['code']) ?></span>
                — <?= htmlspecialchars($inf['libelle']) ?>
              </label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ── Qualification du Substitut ──────────────────────────────────── -->
  <?php if ($isSubstitut): ?>
  <div class="card border-0 shadow-sm mb-3 border-start border-danger border-4">
    <div class="card-header bg-danger text-white fw-semibold py-2">
      <i class="bi bi-shield-exclamation me-2"></i>Qualification — Substitut du Procureur
      <span class="badge bg-white text-danger ms-2 small">Réservé substitut</span>
    </div>
    <div class="card-body py-2">
      <div class="row g-1">
        <?php foreach ($grouped as $cat => $infs):
              foreach ($infs as $inf): ?>
        <div class="col-sm-6 col-lg-4">
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox"
                   name="infractions_mec_substitut[]" value="<?= $inf['id'] ?>"
                   id="meci_s_<?= $inf['id'] ?>"
                   <?= in_array((int)$inf['id'], $mecInfrSubstitut) ? 'checked' : '' ?>>
            <label class="form-check-label small" for="meci_s_<?= $inf['id'] ?>">
              <span class="fw-semibold text-danger"><?= htmlspecialchars($inf['code']) ?></span>
              — <?= htmlspecialchars($inf['libelle']) ?>
              <span class="badge bg-<?= $catColors[$cat] ?? 'secondary' ?> ms-1" style="font-size:0.6rem"><?= substr($cat,0,3) ?></span>
            </label>
          </div>
        </div>
        <?php endforeach; endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- ── Antécédents judiciaires ─────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold py-2">
      <i class="bi bi-clock-history me-2 text-danger"></i>Antécédents judiciaires
    </div>
    <div class="card-body py-3">
      <div class="row g-2 align-items-center">
        <div class="col-sm-4">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   name="est_connu_archives" id="knownArchives" value="1"
                   <?= !empty($d['est_connu_archives']) ? 'checked' : '' ?>>
            <label class="form-check-label fw-semibold small" for="knownArchives">
              Connu des archives (récidiviste)
            </label>
          </div>
        </div>
        <div class="col-sm-3">
          <label class="form-label fw-semibold small">Nb affaires précédentes</label>
          <input type="number" name="nb_affaires_precedentes" class="form-control form-control-sm"
                 min="0" value="<?= (int)($d['nb_affaires_precedentes'] ?? 0) ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold small">Notes / Antécédents connus</label>
          <textarea name="notes_antecedents" class="form-control form-control-sm" rows="2"
                    placeholder="Résumé des antécédents judiciaires connus..."><?= htmlspecialchars($d['notes_antecedents'] ?? '') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Personne à contacter ───────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold py-2">
      <i class="bi bi-telephone me-2 text-info"></i>Personne à contacter / Famille
    </div>
    <div class="card-body py-3">
      <div class="row g-2">
        <div class="col-sm-5">
          <label class="form-label fw-semibold small">Nom de la personne à contacter</label>
          <input type="text" name="personne_contacter_nom" class="form-control form-control-sm"
                 placeholder="Nom Prénom" value="<?= htmlspecialchars($d['personne_contacter_nom'] ?? '') ?>">
        </div>
        <div class="col-sm-3">
          <label class="form-label fw-semibold small">Téléphone</label>
          <input type="tel" name="personne_contacter_tel" class="form-control form-control-sm"
                 placeholder="+227..." value="<?= htmlspecialchars($d['personne_contacter_tel'] ?? '') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold small">Lien de parenté</label>
          <input type="text" name="personne_contacter_lien" class="form-control form-control-sm"
                 placeholder="Père, Mère, Époux(se)..." value="<?= htmlspecialchars($d['personne_contacter_lien'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Photo d'identité ───────────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold py-2">
      <i class="bi bi-camera me-2 text-secondary"></i>Photo d'identité
    </div>
    <div class="card-body py-3">
      <div class="row g-2 align-items-center">
        <?php if (!empty($d['photo'])): ?>
        <div class="col-auto">
          <img src="<?= BASE_URL ?>/<?= htmlspecialchars($d['photo']) ?>"
               alt="Photo" class="rounded border" style="height:80px;width:60px;object-fit:cover;">
        </div>
        <?php endif; ?>
        <div class="col">
          <label class="form-label fw-semibold small">Photo <small class="text-muted">JPG/PNG ≤ 2 Mo</small></label>
          <input type="file" name="photo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png">
        </div>
      </div>
    </div>
  </div>

  <!-- Boutons -->
  <div class="<?= $_inModal ? 'sticky-bottom bg-white border-top p-3 mt-auto' : 'd-flex gap-2 justify-content-end mt-3' ?>">
    <div class="d-flex gap-2 justify-content-end flex-wrap">
      <?php if (!$_inModal): ?>
      <a href="<?= BASE_URL ?>/pv/show/<?= htmlspecialchars($pvId ?? ($d['pv_id'] ?? '')) ?>#mises-en-cause"
         class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-x-lg me-1"></i>Annuler
      </a>
      <?php else: ?>
      <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
        <i class="bi bi-x-lg me-1"></i>Annuler
      </button>
      <?php endif; ?>
      <button type="submit" class="btn btn-warning btn-sm px-4">
        <i class="bi bi-person-exclamation me-2"></i><?= $btnLabel ?? 'Enregistrer' ?>
      </button>
    </div>
  </div>
</form>

<script>
function toggleStatutAutre(val) {
    var el = document.getElementById('rowStatutAutre');
    if (el) el.style.display = (val === 'autre') ? 'block' : 'none';
}
</script>
