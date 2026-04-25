<?php $pageTitle = 'Rapports générés'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Rapports générés</h4>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= implode('<br>', array_map('htmlspecialchars', (array)$flash['success'])) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($rapports)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-file-earmark-x display-4 d-block mb-2 opacity-25"></i>
            Aucun rapport généré. Utilisez le tableau de bord pour en créer un.
        </div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th>Généré par</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rapports as $r): ?>
            <tr>
                <td class="text-muted small"><?= $r['id'] ?></td>
                <td><strong><?= htmlspecialchars($r['titre']) ?></strong></td>
                <td>
                    <?php $typeBadge = ['quotidien'=>'primary','hebdomadaire'=>'info','mensuel'=>'success','annuel'=>'warning','personnalise'=>'secondary']; ?>
                    <span class="badge bg-<?= $typeBadge[$r['type']] ?? 'secondary' ?>"><?= ucfirst($r['type']) ?></span>
                </td>
                <td class="small">
                    <?= date('d/m/Y', strtotime($r['date_debut'])) ?>
                    <?php if ($r['date_debut'] !== $r['date_fin']): ?>
                    → <?= date('d/m/Y', strtotime($r['date_fin'])) ?>
                    <?php endif; ?>
                </td>
                <td class="small"><?= htmlspecialchars(($r['gen_prenom'] ?? '') . ' ' . ($r['gen_nom'] ?? '')) ?></td>
                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-3">
    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour au tableau de bord
    </a>
</div>
