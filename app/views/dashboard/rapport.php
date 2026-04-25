<?php $pageTitle = 'Rapport — ' . htmlspecialchars($rapport['titre']); ?>

<div class="d-flex align-items-center mb-4 mt-2 gap-3">
    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
    <h4 class="fw-bold mb-0">
        <i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>
        <?= htmlspecialchars($rapport['titre']) ?>
    </h4>
    <button class="btn btn-sm btn-outline-secondary ms-auto" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>Imprimer
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-2 text-primary"><?= (int)($data['pv']['total'] ?? 0) ?></div>
            <small class="text-muted">PVs reçus</small>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-2 text-danger"><?= (int)($data['pv']['antiterro'] ?? 0) ?></div>
            <small class="text-muted">Affaires antiterroristes</small>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-2 text-success"><?= (int)($data['dossiers']['total'] ?? 0) ?></div>
            <small class="text-muted">Dossiers créés</small>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-2 text-warning"><?= (int)($data['mec']['total'] ?? 0) ?></div>
            <small class="text-muted">Mises en cause</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-file-text me-2 text-primary"></i>PVs — Détail
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><td>Pénal</td><td class="fw-bold"><?= (int)($data['pv']['penale'] ?? 0) ?></td></tr>
                    <tr><td>Civil</td><td class="fw-bold"><?= (int)($data['pv']['civile'] ?? 0) ?></td></tr>
                    <tr><td>Commercial</td><td class="fw-bold"><?= (int)($data['pv']['commerciale'] ?? 0) ?></td></tr>
                    <tr class="table-danger"><td>Antiterroristes</td><td class="fw-bold"><?= (int)($data['pv']['antiterro'] ?? 0) ?></td></tr>
                    <tr class="table-secondary"><td>Classés sans suite</td><td class="fw-bold"><?= (int)($data['pv']['classes'] ?? 0) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-people me-2 text-warning"></i>Mises en cause — Décisions
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><td>Total</td><td class="fw-bold"><?= (int)($data['mec']['total'] ?? 0) ?></td></tr>
                    <tr class="table-success"><td>Poursuivis</td><td class="fw-bold"><?= (int)($data['mec']['poursuivis'] ?? 0) ?></td></tr>
                    <tr class="table-danger"><td>Non poursuivis</td><td class="fw-bold"><?= (int)($data['mec']['non_poursuivis'] ?? 0) ?></td></tr>
                    <tr><td>Femmes</td><td class="fw-bold"><?= (int)($data['mec']['femmes'] ?? 0) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($data['top_infractions'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold border-bottom">
        <i class="bi bi-gavel me-2 text-danger"></i>Top 5 infractions les plus fréquentes
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>#</th><th>Infraction</th><th class="text-end">Nombre</th></tr></thead>
            <tbody>
            <?php foreach ($data['top_infractions'] as $i => $inf): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($inf['libelle']) ?></td>
                <td class="text-end fw-bold"><?= (int)$inf['nb'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($data['pv_par_jour'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold border-bottom">
        <i class="bi bi-bar-chart me-2 text-info"></i>PVs reçus par jour
    </div>
    <div class="card-body"><canvas id="pvJourChart" height="60"></canvas></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    var data = <?= json_encode(array_values($data['pv_par_jour']), JSON_UNESCAPED_UNICODE) ?>;
    var labels = data.map(d => d.jour);
    var vals   = data.map(d => parseInt(d.nb));
    new Chart(document.getElementById('pvJourChart'), {
        type: 'bar',
        data: { labels, datasets:[{label:'PVs reçus', data:vals, backgroundColor:'rgba(54,162,235,0.7)'}] },
        options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{stepSize:1}}} }
    });
})();
</script>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Rapport généré le <?= date('d/m/Y à H:i', strtotime($rapport['created_at'])) ?>
        par <?= htmlspecialchars(($rapport['generateur_prenom'] ?? '') . ' ' . ($rapport['generateur_nom'] ?? '')) ?>
        — Période : <?= date('d/m/Y', strtotime($data['date_debut'])) ?> au <?= date('d/m/Y', strtotime($data['date_fin'])) ?>
    </div>
</div>
