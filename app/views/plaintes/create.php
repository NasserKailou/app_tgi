<?php $pageTitle = 'Nouvelle plainte'; ?>
<div class="mb-4 mt-2">
  <nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/plaintes">Plaintes</a></li>
    <li class="breadcrumb-item active">Nouvelle plainte</li>
  </ol></nav>
  <h4 class="fw-bold"><i class="bi bi-megaphone-fill me-2 text-primary"></i>Enregistrer une nouvelle plainte</h4>
</div>

<?php if (!empty($flash['error'])): foreach ($flash['error'] as $msg): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; endif; ?>

<form method="POST" action="<?= BASE_URL ?>/plaintes/store" enctype="multipart/form-data" novalidate>
  <?= CSRF::field() ?>
  <div class="row g-4">
    <!-- Colonne gauche -->
    <div class="col-lg-8">

      <!-- Identification de la plainte -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-hash me-2 text-primary"></i>Identification</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label fw-semibold">N° Plainte</label>
              <input type="text" name="numero_plainte" class="form-control font-monospace text-uppercase"
                     placeholder="Auto-généré si vide"
                     value="<?= htmlspecialchars($_POST['numero_plainte'] ?? '') ?>">
              <div class="form-text">Laissez vide pour auto-génération</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Date de la plainte <span class="text-danger">*</span></label>
              <input type="date" name="date_plainte" class="form-control" required
                     value="<?= htmlspecialchars($_POST['date_plainte'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Date de réception <span class="text-danger">*</span></label>
              <input type="date" name="date_reception" class="form-control" required
                     value="<?= htmlspecialchars($_POST['date_reception'] ?? date('Y-m-d')) ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Informations du plaignant -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-person-fill me-2 text-info"></i>Plaignant</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
              <input type="text" name="plaignant_nom" class="form-control text-uppercase" required
                     placeholder="NOM DE FAMILLE"
                     value="<?= htmlspecialchars($_POST['plaignant_nom'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Prénom</label>
              <input type="text" name="plaignant_prenom" class="form-control"
                     value="<?= htmlspecialchars($_POST['plaignant_prenom'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Qualité</label>
              <select name="plaignant_qualite" class="form-select">
                <option value="personne_physique" <?= ($_POST['plaignant_qualite'] ?? 'personne_physique') === 'personne_physique' ? 'selected' : '' ?>>Personne physique</option>
                <option value="personne_morale"   <?= ($_POST['plaignant_qualite'] ?? '') === 'personne_morale' ? 'selected' : '' ?>>Personne morale</option>
                <option value="administration"    <?= ($_POST['plaignant_qualite'] ?? '') === 'administration' ? 'selected' : '' ?>>Administration</option>
                <option value="autre"             <?= ($_POST['plaignant_qualite'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Téléphone</label>
              <input type="tel" name="plaignant_telephone" class="form-control"
                     value="<?= htmlspecialchars($_POST['plaignant_telephone'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" name="plaignant_email" class="form-control"
                     value="<?= htmlspecialchars($_POST['plaignant_email'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Adresse</label>
              <input type="text" name="plaignant_adresse" class="form-control"
                     value="<?= htmlspecialchars($_POST['plaignant_adresse'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Mis en cause dans la plainte -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-person-dash me-2 text-warning"></i>Personne mise en cause dans la plainte</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom du mis en cause</label>
              <input type="text" name="mis_en_cause_nom" class="form-control"
                     value="<?= htmlspecialchars($_POST['mis_en_cause_nom'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Adresse connue</label>
              <input type="text" name="mis_en_cause_adresse" class="form-control"
                     value="<?= htmlspecialchars($_POST['mis_en_cause_adresse'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Faits et nature -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-journal-text me-2 text-danger"></i>Nature et faits</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Nature de la plainte <span class="text-danger">*</span></label>
              <input type="text" name="nature_plainte" class="form-control" required
                     placeholder="Ex: Vol avec violence, Escroquerie, Coups et blessures..."
                     value="<?= htmlspecialchars($_POST['nature_plainte'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Lieu des faits</label>
              <input type="text" name="lieu_faits" class="form-control"
                     value="<?= htmlspecialchars($_POST['lieu_faits'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Date des faits</label>
              <input type="date" name="date_faits" class="form-control"
                     value="<?= htmlspecialchars($_POST['date_faits'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description des faits</label>
              <textarea name="description_faits" class="form-control" rows="4"
                        placeholder="Décrire les faits en détail..."><?= htmlspecialchars($_POST['description_faits'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Pièces jointes <small class="text-muted">PDF, JPG, PNG, DOC ≤ 10 Mo</small></label>
              <input type="file" name="piece_jointe" class="form-control"
                     accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            </div>
          </div>
        </div>
      </div>

    </div><!-- /.col-lg-8 -->

    <!-- Colonne droite -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-person-badge me-2 text-secondary"></i>Affectation</div>
        <div class="card-body">
          <label class="form-label fw-semibold">Substitut chargé</label>
          <select name="substitut_id" class="form-select">
            <option value="">— Non affecté —</option>
            <?php foreach ($substituts as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($_POST['substitut_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div class="mt-3">
            <label class="form-label fw-semibold">Observations internes</label>
            <textarea name="observations" class="form-control" rows="3"
                      placeholder="Notes internes..."><?= htmlspecialchars($_POST['observations'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm bg-light">
        <div class="card-body text-center py-4">
          <button type="submit" class="btn btn-primary px-5">
            <i class="bi bi-check-circle me-2"></i>Enregistrer la plainte
          </button>
          <div class="mt-2">
            <a href="<?= BASE_URL ?>/plaintes" class="text-muted small">
              <i class="bi bi-arrow-left me-1"></i>Annuler
            </a>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.row -->
</form>
