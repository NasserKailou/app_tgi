<?php $pageTitle = 'Modifier la mise en cause'; ?>
<div class="mb-4 mt-2">
  <nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv">PV</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv/show/<?= $mec['pv_id'] ?>">PV #<?= htmlspecialchars($mec['numero_rg']) ?></a></li>
    <li class="breadcrumb-item active">Modifier mise en cause</li>
  </ol></nav>
  <h4 class="fw-bold"><i class="bi bi-person-exclamation me-2 text-warning"></i>
    Modifier — <?= htmlspecialchars($mec['nom'] . ' ' . $mec['prenom']) ?>
  </h4>
</div>

<?php if (!empty($flash['error'])): foreach ($flash['error'] as $msg): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; endif; ?>

<?php
$formAction = BASE_URL . '/pv/mise-en-cause/update/' . $mec['id'];
$pvId       = $mec['pv_id'];
$btnLabel   = 'Mettre à jour';
require __DIR__ . '/_form.php';
?>
