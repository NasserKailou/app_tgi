<?php $pageTitle = 'Modifier la plainte ' . htmlspecialchars($plainte['numero_plainte']); ?>
<div class="mb-4 mt-2">
  <nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/plaintes">Plaintes</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/plaintes/show/<?= $plainte['id'] ?>"><?= htmlspecialchars($plainte['numero_plainte']) ?></a></li>
    <li class="breadcrumb-item active">Modifier</li>
  </ol></nav>
  <h4 class="fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier la plainte <?= htmlspecialchars($plainte['numero_plainte']) ?></h4>
</div>

<form method="POST" action="<?= BASE_URL ?>/plaintes/update/<?= $plainte['id'] ?>" novalidate>
  <?= CSRF::field() ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Dates</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date de la plainte</label>
              <input type="date" name="date_plainte" class="form-control"
                     value="<?= htmlspecialchars($plainte['date_plainte']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date de réception</label>
              <input type="date" name="date_reception" class="form-control"
                     value="<?= htmlspecialchars($plainte['date_reception']) ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-person-fill me-2 text-info"></i>Plaignant</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label fw-semibold">Nom *</label>
              <input type="text" name="plaignant_nom" class="form-control text-uppercase" required
                     value="<?= htmlspecialchars($plainte['plaignant_nom']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Prénom</label>
              <input type="text" name="plaignant_prenom" class="form-control"
                     value="<?= htmlspecialchars($plainte['plaignant_prenom'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Qualité</label>
              <select name="plaignant_qualite" class="form-select">
                <option value="personne_physique" <?= ($plainte['plaignant_qualite'] ?? '') === 'personne_physique' ? 'selected' : '' ?>>Pers. physique</option>
                <option value="personne_morale"   <?= ($plainte['plaignant_qualite'] ?? '') === 'personne_morale' ? 'selected' : '' ?>>Pers. morale</option>
                <option value="administration"    <?= ($plainte['plaignant_qualite'] ?? '') === 'administration' ? 'selected' : '' ?>>Administration</option>
                <option value="autre"             <?= ($plainte['plaignant_qualite'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Téléphone</label>
              <input type="tel" name="plaignant_telephone" class="form-control"
                     value="<?= htmlspecialchars($plainte['plaignant_telephone'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" name="plaignant_email" class="form-control"
                     value="<?= htmlspecialchars($plainte['plaignant_email'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Adresse</label>
              <input type="text" name="plaignant_adresse" class="form-control"
                     value="<?= htmlspecialchars($plainte['plaignant_adresse'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-journal-text me-2 text-danger"></i>Faits</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Mis en cause (nom)</label>
              <input type="text" name="mis_en_cause_nom" class="form-control"
                     value="<?= htmlspecialchars($plainte['mis_en_cause_nom'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Adresse du mis en cause</label>
              <input type="text" name="mis_en_cause_adresse" class="form-control"
                     value="<?= htmlspecialchars($plainte['mis_en_cause_adresse'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Nature de la plainte *</label>
              <input type="text" name="nature_plainte" class="form-control" required
                     value="<?= htmlspecialchars($plainte['nature_plainte']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Lieu des faits</label>
              <input type="text" name="lieu_faits" class="form-control"
                     value="<?= htmlspecialchars($plainte['lieu_faits'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Date des faits</label>
              <input type="date" name="date_faits" class="form-control"
                     value="<?= htmlspecialchars($plainte['date_faits'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="description_faits" class="form-control" rows="4"><?= htmlspecialchars($plainte['description_faits'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Affectation</div>
        <div class="card-body">
          <label class="form-label fw-semibold">Substitut</label>
          <select name="substitut_id" class="form-select">
            <option value="">— Non affecté —</option>
            <?php foreach ($substituts as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($plainte['substitut_id'] == $s['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div class="mt-3">
            <label class="form-label fw-semibold">Observations</label>
            <textarea name="observations" class="form-control" rows="3"><?= htmlspecialchars($plainte['observations'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-floppy me-2"></i>Enregistrer</button>
        <a href="<?= BASE_URL ?>/plaintes/show/<?= $plainte['id'] ?>" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </div>
  </div>
</form>
