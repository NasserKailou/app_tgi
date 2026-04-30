<?php
$pageTitle = 'Plainte ' . htmlspecialchars($plainte['numero_plainte']);
$badges = ['deposee'=>'bg-secondary','en_examen'=>'bg-info','transmise_pv'=>'bg-success','classee'=>'bg-dark','irrecevable'=>'bg-danger'];
$labels = ['deposee'=>'Déposée','en_examen'=>'En examen','transmise_pv'=>'Transmise (PV)','classee'=>'Classée','irrecevable'=>'Irrecevable'];
$bs = $badges[$plainte['statut']] ?? 'bg-secondary';
$lb = $labels[$plainte['statut']] ?? $plainte['statut'];
?>
<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/plaintes">Plaintes</a></li>
      <li class="breadcrumb-item active"><?= htmlspecialchars($plainte['numero_plainte']) ?></li>
    </ol></nav>
    <h4 class="fw-bold mb-0">
      <i class="bi bi-megaphone me-2 text-primary"></i>
      Plainte <?= htmlspecialchars($plainte['numero_plainte']) ?>
      <span class="badge <?= $bs ?> ms-2 fs-6"><?= $lb ?></span>
    </h4>
  </div>
  <div class="d-flex gap-2">
    <?php if (!in_array($plainte['statut'], ['transmise_pv','classee','irrecevable'])): ?>
    <a href="<?= BASE_URL ?>/plaintes/edit/<?= $plainte['id'] ?>" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-pencil me-1"></i>Modifier
    </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/plaintes" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
  </div>
</div>

<?php if (!empty($flash['success'])): foreach ($flash['success'] as $msg): ?>
<div class="alert alert-success alert-dismissible fade show">
  <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; endif; ?>

<div class="row g-4">
  <!-- Infos principales -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold"><i class="bi bi-person-fill me-2 text-info"></i>Plaignant</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Nom complet</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($plainte['plaignant_nom'] . ' ' . $plainte['plaignant_prenom']) ?></dd>
          <dt class="col-sm-4">Qualité</dt>
          <dd class="col-sm-8"><?= ucfirst(str_replace('_', ' ', $plainte['plaignant_qualite'] ?? '')) ?></dd>
          <?php if ($plainte['plaignant_telephone']): ?>
          <dt class="col-sm-4">Téléphone</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($plainte['plaignant_telephone']) ?></dd>
          <?php endif; ?>
          <?php if ($plainte['plaignant_email']): ?>
          <dt class="col-sm-4">Email</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($plainte['plaignant_email']) ?></dd>
          <?php endif; ?>
          <?php if ($plainte['plaignant_adresse']): ?>
          <dt class="col-sm-4">Adresse</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($plainte['plaignant_adresse']) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold"><i class="bi bi-journal-text me-2 text-danger"></i>Faits</div>
      <div class="card-body">
        <p class="fw-semibold mb-1">Nature : <?= htmlspecialchars($plainte['nature_plainte']) ?></p>
        <?php if ($plainte['lieu_faits']): ?><p class="text-muted small mb-1">Lieu : <?= htmlspecialchars($plainte['lieu_faits']) ?></p><?php endif; ?>
        <?php if ($plainte['date_faits']): ?><p class="text-muted small mb-2">Date des faits : <?= date('d/m/Y', strtotime($plainte['date_faits'])) ?></p><?php endif; ?>
        <?php if ($plainte['description_faits']): ?>
        <p class="mb-0"><?= nl2br(htmlspecialchars($plainte['description_faits'])) ?></p>
        <?php endif; ?>
        <?php if ($plainte['mis_en_cause_nom']): ?>
        <hr>
        <p class="mb-0"><strong>Personne mise en cause :</strong> <?= htmlspecialchars($plainte['mis_en_cause_nom']) ?>
          <?php if ($plainte['mis_en_cause_adresse']): ?> — <?= htmlspecialchars($plainte['mis_en_cause_adresse']) ?><?php endif; ?>
        </p>
        <?php endif; ?>
        <?php if ($plainte['pieces_jointes']): ?>
        <hr>
        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($plainte['pieces_jointes']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-paperclip me-1"></i>Voir la pièce jointe
        </a>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($plainte['pv_numero_rg']): ?>
    <div class="card border-0 shadow-sm border-success mb-4">
      <div class="card-body">
        <i class="bi bi-link-45deg text-success me-2"></i>
        PV associé : <a href="<?= BASE_URL ?>/pv/show/<?= $plainte['pv_id'] ?>" class="fw-bold">
          <?= htmlspecialchars($plainte['pv_numero_rg']) ?>
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Colonne latérale actions -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Infos</div>
      <div class="card-body">
        <dl class="row mb-0 small">
          <dt class="col-6">N° Plainte</dt>
          <dd class="col-6 font-monospace"><?= htmlspecialchars($plainte['numero_plainte']) ?></dd>
          <dt class="col-6">Date</dt>
          <dd class="col-6"><?= date('d/m/Y', strtotime($plainte['date_plainte'])) ?></dd>
          <dt class="col-6">Réception</dt>
          <dd class="col-6"><?= date('d/m/Y', strtotime($plainte['date_reception'])) ?></dd>
          <dt class="col-6">Substitut</dt>
          <dd class="col-6">
            <?= $plainte['sub_nom'] ? htmlspecialchars($plainte['sub_prenom'] . ' ' . $plainte['sub_nom']) : '—' ?>
          </dd>
          <?php if ($plainte['motif_classement']): ?>
          <dt class="col-6">Motif</dt>
          <dd class="col-6"><?= htmlspecialchars($plainte['motif_classement']) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <!-- Actions -->
    <?php if (!in_array($plainte['statut'], ['transmise_pv','classee','irrecevable'])): ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold"><i class="bi bi-lightning-charge me-2 text-warning"></i>Actions</div>
      <div class="card-body d-grid gap-2">
        <?php if ($plainte['statut'] === 'deposee'): ?>
        <!-- Affecter substitut & passer en examen -->
        <form method="POST" action="<?= BASE_URL ?>/plaintes/traiter/<?= $plainte['id'] ?>">
          <?= CSRF::field() ?>
          <select name="substitut_id" class="form-select form-select-sm mb-2" required>
            <option value="">— Sélectionner substitut —</option>
            <?php foreach ($substituts as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($plainte['substitut_id'] == $s['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-info btn-sm w-100">
            <i class="bi bi-arrow-right-circle me-1"></i>Mettre en examen
          </button>
        </form>
        <hr class="my-2">
        <?php endif; ?>

        <!-- Créer un PV depuis la plainte -->
        <?php if (!$plainte['pv_id']): ?>
        <form method="POST" action="<?= BASE_URL ?>/plaintes/creer-pv/<?= $plainte['id'] ?>">
          <?= CSRF::field() ?>
          <button type="submit" class="btn btn-success btn-sm w-100"
                  onclick="return confirm('Créer un PV depuis cette plainte ?')">
            <i class="bi bi-file-plus me-1"></i>Créer un PV
          </button>
        </form>
        <hr class="my-2">
        <?php endif; ?>

        <!-- Classer sans suite -->
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalClasser">
          <i class="bi bi-archive me-1"></i>Classer / Irrecevable
        </button>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Classement -->
<div class="modal fade" id="modalClasser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= BASE_URL ?>/plaintes/classer/<?= $plainte['id'] ?>">
        <?= CSRF::field() ?>
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-archive me-2"></i>Classer la plainte</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Décision</label>
            <select name="statut_classement" class="form-select" required>
              <option value="classee">Classée sans suite</option>
              <option value="irrecevable">Irrecevable</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Motif <span class="text-danger">*</span></label>
            <textarea name="motif_classement" class="form-control" rows="3" required
                      placeholder="Indiquer le motif du classement..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-secondary">
            <i class="bi bi-check-lg me-1"></i>Confirmer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
