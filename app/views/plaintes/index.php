<?php $pageTitle = 'Gestion des Plaintes — Parquet'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="bi bi-megaphone me-2 text-primary"></i>Plaintes</h4>
    <small class="text-muted">Parquet — Gestion des plaintes reçues</small>
  </div>
  <?php if (DroitsController::hasFuncAccess((int)($user['id'] ?? 0), 'plainte_creer')): ?>
  <a href="<?= BASE_URL ?>/plaintes/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nouvelle plainte
  </a>
  <?php endif; ?>
</div>

<?php if (!empty($flash['success'])): foreach ($flash['success'] as $msg): ?>
<div class="alert alert-success alert-dismissible fade show">
  <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; endif; ?>
<?php if (!empty($flash['error'])): foreach ($flash['error'] as $msg): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; endif; ?>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-center">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Rechercher par numéro, plaignant, nature..."
                 value="<?= htmlspecialchars($search ?? '') ?>">
        </div>
      </div>
      <div class="col-md-3">
        <select name="statut" class="form-select">
          <option value="">— Tous statuts —</option>
          <?php foreach (['deposee' => 'Déposée', 'en_examen' => 'En examen', 'transmise_pv' => 'Transmise (PV créé)', 'classee' => 'Classée', 'irrecevable' => 'Irrecevable'] as $v => $l): ?>
          <option value="<?= $v ?>" <?= ($statut ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrer</button>
        <a href="<?= BASE_URL ?>/plaintes" class="btn btn-outline-secondary ms-1">Réinitialiser</a>
      </div>
      <div class="col-auto ms-auto">
        <span class="text-muted small"><?= number_format($total) ?> plainte(s) trouvée(s)</span>
      </div>
    </form>
  </div>
</div>

<!-- Tableau -->
<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>N° Plainte</th>
          <th>Date</th>
          <th>Plaignant</th>
          <th>Nature</th>
          <th>Substitut chargé</th>
          <th>Statut</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($plaintes)): ?>
        <tr><td colspan="7" class="text-center py-5 text-muted">
          <i class="bi bi-inbox display-4 d-block mb-2"></i>Aucune plainte enregistrée
        </td></tr>
        <?php else: ?>
        <?php foreach ($plaintes as $pl): ?>
        <tr>
          <td><span class="font-monospace fw-semibold"><?= htmlspecialchars($pl['numero_plainte']) ?></span></td>
          <td><?= date('d/m/Y', strtotime($pl['date_plainte'])) ?></td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($pl['plaignant_nom'] . ' ' . $pl['plaignant_prenom']) ?></div>
            <?php if ($pl['plaignant_telephone']): ?>
            <small class="text-muted"><?= htmlspecialchars($pl['plaignant_telephone']) ?></small>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars(mb_strimwidth($pl['nature_plainte'], 0, 50, '...')) ?></td>
          <td>
            <?= $pl['sub_nom'] ? htmlspecialchars($pl['sub_prenom'] . ' ' . $pl['sub_nom']) : '<span class="text-muted">Non assigné</span>' ?>
          </td>
          <td>
            <?php
            $badges = [
              'deposee'     => 'bg-secondary',
              'en_examen'   => 'bg-info',
              'transmise_pv'=> 'bg-success',
              'classee'     => 'bg-dark',
              'irrecevable' => 'bg-danger',
            ];
            $labels = [
              'deposee'     => 'Déposée',
              'en_examen'   => 'En examen',
              'transmise_pv'=> 'PV créé',
              'classee'     => 'Classée',
              'irrecevable' => 'Irrecevable',
            ];
            $bs = $badges[$pl['statut']] ?? 'bg-secondary';
            $lb = $labels[$pl['statut']] ?? $pl['statut'];
            ?>
            <span class="badge <?= $bs ?>"><?= $lb ?></span>
          </td>
          <td class="text-center">
            <a href="<?= BASE_URL ?>/plaintes/show/<?= $pl['id'] ?>"
               class="btn btn-sm btn-outline-primary" title="Voir">
              <i class="bi bi-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Pagination -->
<?php if (($totalPages ?? 1) > 1): ?>
<nav class="mt-4">
  <ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
      <a class="page-link" href="?page=<?= $p ?>&q=<?= urlencode($search ?? '') ?>&statut=<?= urlencode($statut ?? '') ?>">
        <?= $p ?>
      </a>
    </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>
