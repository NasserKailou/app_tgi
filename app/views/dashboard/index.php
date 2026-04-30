<?php $pageTitle = 'Tableau de bord'; ?>

<!-- Stats Cards -->
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Tableau de bord</h4>
    <small class="text-muted"><?= date('l d MMMM Y') ?> — <?= date('H:i') ?></small>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-blue">
            <div class="stat-icon"><i class="bi bi-file-text-fill"></i></div>
            <div class="stat-value"><?= $stats['pvMois'] ?></div>
            <div class="stat-label">PV reçus ce mois</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-orange">
            <div class="stat-icon"><i class="bi bi-folder2-open"></i></div>
            <div class="stat-value"><?= $stats['dossiersEnCours'] ?></div>
            <div class="stat-label">Dossiers en cours</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-green">
            <div class="stat-icon"><i class="bi bi-calendar-week"></i></div>
            <div class="stat-value"><?= $stats['audiencesSemaine'] ?></div>
            <div class="stat-label">Audiences cette semaine</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-red">
            <div class="stat-icon"><i class="bi bi-person-lock"></i></div>
            <div class="stat-value"><?= $stats['population'] ?></div>
            <div class="stat-label">Population carcérale</div>
        </div>
    </div>
</div>

<?php if ($stats['nbAlertesTotal'] > 0): ?>
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div><strong><?= $stats['nbAlertesTotal'] ?> alerte<?= $stats['nbAlertesTotal'] > 1 ? 's' : '' ?> non lue<?= $stats['nbAlertesTotal'] > 1 ? 's' : '' ?></strong> nécessite<?= $stats['nbAlertesTotal'] > 1 ? 'nt' : '' ?> votre attention.</div>
    <a href="<?= BASE_URL ?>/alertes" class="btn btn-warning btn-sm ms-auto">Voir les alertes</a>
</div>
<?php endif; ?>

<!-- Nouveaux modules — stats complémentaires -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <a href="<?=BASE_URL?>/avocats" class="text-decoration-none">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="fw-bold fs-4 text-primary"><?= $stats['nbAvocats'] ?? 0 ?></div>
            <small class="text-muted">Avocats actifs</small>
        </div></a>
    </div>
    <div class="col-6 col-md-2">
        <a href="<?=BASE_URL?>/controles-judiciaires" class="text-decoration-none">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="fw-bold fs-4 text-success"><?= $stats['nbControlesActifs'] ?? 0 ?></div>
            <small class="text-muted">Contrôles judiciaires</small>
        </div></a>
    </div>
    <div class="col-6 col-md-2">
        <a href="<?=BASE_URL?>/expertises" class="text-decoration-none">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="fw-bold fs-4 text-info"><?= $stats['nbExpertisesEnCours'] ?? 0 ?></div>
            <small class="text-muted">Expertises en cours</small>
        </div></a>
    </div>
    <div class="col-6 col-md-2">
        <a href="<?=BASE_URL?>/scelles" class="text-decoration-none">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="fw-bold fs-4 text-warning"><?= $stats['nbScelles'] ?? 0 ?></div>
            <small class="text-muted">Scellés conservés</small>
        </div></a>
    </div>
    <div class="col-6 col-md-2">
        <a href="<?=BASE_URL?>/voies-recours" class="text-decoration-none">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="fw-bold fs-4 text-danger"><?= $stats['nbVoiesRecours'] ?? 0 ?></div>
            <small class="text-muted">Recours en cours</small>
        </div></a>
    </div>
    <div class="col-6 col-md-2">
        <a href="<?=BASE_URL?>/ordonnances" class="text-decoration-none">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="fw-bold fs-4 text-secondary"><?= $stats['nbOrdonnances'] ?? 0 ?></div>
            <small class="text-muted">Ordonnances <?=date('Y')?></small>
        </div></a>
    </div>
</div>

<!-- Génération de rapport -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Générer un rapport</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/dashboard/rapport">
            <?= CSRF::field() ?>
            <div class="row g-2 align-items-end">
                <div class="col-sm-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="type_rapport" id="typeRapport" class="form-select form-select-sm"
                            onchange="updateRapportDates(this.value)">
                        <option value="quotidien">Quotidien (aujourd'hui)</option>
                        <option value="hebdomadaire">Hebdomadaire (7 jours)</option>
                        <option value="mensuel">Mensuel (ce mois)</option>
                        <option value="annuel">Annuel (<?= date('Y') ?>)</option>
                        <option value="personnalise">Personnalisé</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="form-label small fw-semibold">Début</label>
                    <input type="date" name="date_debut" id="rapportDateDebut"
                           class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-sm-2">
                    <label class="form-label small fw-semibold">Fin</label>
                    <input type="date" name="date_fin" id="rapportDateFin"
                           class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-bar-chart-line me-1"></i>Générer le rapport
                    </button>
                </div>
            </div>
        </form>
        <?php if (!empty($dernierRapports)): ?>
        <hr class="my-3">
        <div class="small text-muted fw-semibold mb-2">Rapports récents :</div>
        <div class="list-group list-group-flush">
        <?php foreach ($dernierRapports as $r): ?>
        <a href="<?= BASE_URL ?>/dashboard/rapport/<?= $r['id'] ?>"
           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-1 px-2">
            <span class="small"><?= htmlspecialchars($r['titre']) ?></span>
            <span class="badge bg-secondary rounded-pill small"><?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
        </a>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Graphiques -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart me-2"></i>PV reçus par mois (12 derniers mois) — par type</h6>
                <small class="text-muted">Pénal / Civil / Commercial / Anti-T</small>
            </div>
            <div class="card-body"><canvas id="pvParMoisChart" height="80"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold"><i class="bi bi-pie-chart me-2"></i>Dossiers par statut</h6></div>
            <div class="card-body"><canvas id="dossierStatutChart"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart-fill me-2"></i>Population carcérale par type</h6></div>
            <div class="card-body"><canvas id="populationChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-event me-2"></i>Prochaines audiences (7 jours)</h6>
                <a href="<?= BASE_URL ?>/audiences" class="btn btn-sm btn-outline-primary">Tout voir</a>
            </div>
            <div class="card-body p-0">
            <?php if (empty($prochainesAudiences)): ?>
                <div class="text-center text-muted py-4"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucune audience planifiée</div>
            <?php else: ?>
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Dossier</th><th>Date</th><th>Type</th><th>Président</th><th>Salle</th></tr></thead>
                    <tbody>
                    <?php foreach ($prochainesAudiences as $aud): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/audiences/show/<?= $aud['id'] ?>" class="text-decoration-none fw-semibold"><?= htmlspecialchars($aud['numero_rg']) ?></a></td>
                        <td><?= date('d/m/Y H:i', strtotime($aud['date_audience'])) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($aud['type_audience']) ?></span></td>
                        <td><?= htmlspecialchars($aud['president_prenom'] . ' ' . $aud['president_nom']) ?></td>
                        <td><?= htmlspecialchars($aud['salle_nom'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Derniers PV -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-file-text me-2"></i>Derniers PV enregistrés</h6>
        <a href="<?= BASE_URL ?>/pv/create" class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>Nouveau PV</a>
    </div>
    <div class="card-body p-0">
    <?php if (empty($derniersPV)): ?>
        <div class="text-center text-muted py-4">Aucun PV enregistré</div>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>N° RG</th><th>N° PV</th><th>Date réception</th><th>Type</th><th>Unité</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($derniersPV as $pv): ?>
            <tr>
                <td><a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($pv['numero_rg']) ?></a></td>
                <td><?= htmlspecialchars($pv['numero_pv']) ?></td>
                <td><?= date('d/m/Y', strtotime($pv['date_reception'])) ?></td>
                <td>
                    <span class="badge <?= $pv['type_affaire']==='penale'?'bg-danger':($pv['type_affaire']==='civile'?'bg-primary':'bg-success') ?>">
                        <?= ucfirst($pv['type_affaire']) ?>
                    </span>
                    <?php if ($pv['est_antiterroriste']): ?><span class="badge bg-dark ms-1"><i class="bi bi-shield-exclamation"></i> Anti-T</span><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($pv['unite_nom'] ?? '—') ?></td>
                <td><?php echo statutBadgePV($pv['statut']); ?></td>
                <td><a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="btn btn-xs btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
    </div>
</div>

<?php
// Préparer les données pour Chart.js
$pvMoisData     = $stats['pvParMois'] ?? [];
$dossierStatuts = $stats['pvStatuts'] ?? [];
$popData        = $stats['detentionTypes'] ?? [];

// Organiser par mois / type — inclure antiterroriste
$moisLabels = []; $pvCivile = []; $pvPenale = []; $pvCommerciale = []; $pvAntiT = [];
$pvByMoisType = [];
foreach ($pvMoisData as $r) {
    $pvByMoisType[$r['mois']][$r['type_affaire']] = $r['nb'];
    $moisLabels[$r['mois']] = true;
}
// Ajouter données antiterroriste depuis la même requête si disponibles
foreach (($stats['pvParMoisAntiT'] ?? []) as $r) {
    $pvByMoisType[$r['mois']]['antiterroriste'] = (int)$r['nb'];
    $moisLabels[$r['mois']] = true;
}
// Générer les 12 derniers mois même sans données
$now = new DateTime();
for ($i = 11; $i >= 0; $i--) {
    $m = (clone $now)->modify("-$i month")->format('Y-m');
    if (!isset($moisLabels[$m])) $moisLabels[$m] = true;
}
ksort($moisLabels);
$moisLabels = array_keys($moisLabels);
foreach ($moisLabels as $m) {
    $pvCivile[]      = intval($pvByMoisType[$m]['civile']          ?? 0);
    $pvPenale[]      = intval($pvByMoisType[$m]['penale']          ?? 0);
    $pvCommerciale[] = intval($pvByMoisType[$m]['commerciale']     ?? 0);
    $pvAntiT[]       = intval($pvByMoisType[$m]['antiterroriste']  ?? 0);
}
// Formatter les labels mois
$moisLabelsFormatted = array_map(function($m) {
    $dt = DateTime::createFromFormat('Y-m', $m);
    return $dt ? $dt->format('M Y') : $m;
}, $moisLabels);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const moisLabels = <?= json_encode($moisLabelsFormatted) ?>;
const pvCivile = <?= json_encode($pvCivile) ?>;
const pvPenale = <?= json_encode($pvPenale) ?>;
const pvCommerciale = <?= json_encode($pvCommerciale) ?>;
const pvAntiT = <?= json_encode($pvAntiT) ?>;

new Chart(document.getElementById('pvParMoisChart'), {
    type:'bar',
    data:{ labels:moisLabels, datasets:[
        {label:'Pénale',      data:pvPenale,      backgroundColor:'rgba(220,53,69,0.8)'},
        {label:'Civile',      data:pvCivile,      backgroundColor:'rgba(13,110,253,0.7)'},
        {label:'Commerciale', data:pvCommerciale, backgroundColor:'rgba(25,135,84,0.7)'},
        {label:'Anti-terroriste', data:pvAntiT,   backgroundColor:'rgba(33,37,41,0.8)'},
    ]},
    options:{
        responsive:true,
        plugins:{
            legend:{position:'bottom'},
            tooltip:{callbacks:{footer:function(items){
                var total=items.reduce((s,i)=>s+i.raw,0);
                return 'Total: '+total;
            }}}
        },
        scales:{x:{stacked:true},y:{stacked:true,beginAtZero:true,ticks:{stepSize:1}}}
    }
});

const dossierStatuts = <?= json_encode(array_column($stats['pvStatuts'], 'nb', 'statut')) ?>;
const dsLabels = Object.keys(dossierStatuts).map(s=>s.replace(/_/g,' '));
const dsData   = Object.values(dossierStatuts);
new Chart(document.getElementById('dossierStatutChart'), {
    type:'doughnut',
    data:{ labels:dsLabels, datasets:[{data:dsData, backgroundColor:['#0d6efd','#ffc107','#198754','#dc3545','#6c757d','#0dcaf0','#fd7e14']}]},
    options:{responsive:true,plugins:{legend:{position:'bottom'}}}
});

const popData = <?= json_encode(array_column($stats['detentionTypes'], 'nb', 'type_detention')) ?>;
new Chart(document.getElementById('populationChart'), {
    type:'bar',
    data:{ labels:Object.keys(popData).map(k=>k.replace(/_/g,' ')), datasets:[{label:'Nombre',data:Object.values(popData),backgroundColor:'rgba(25,135,84,0.8)'}]},
    options:{responsive:true,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true}}}
});

// ── Auto-complétion des dates du rapport ──────────────────────────────
function updateRapportDates(type) {
    var today  = new Date();
    var dd     = document.getElementById('rapportDateDebut');
    var df     = document.getElementById('rapportDateFin');
    var fmt    = d => d.toISOString().split('T')[0];
    if (!dd || !df) return;
    if (type === 'quotidien') {
        dd.value = fmt(today); df.value = fmt(today);
    } else if (type === 'hebdomadaire') {
        var d7 = new Date(today); d7.setDate(d7.getDate()-6);
        dd.value = fmt(d7); df.value = fmt(today);
    } else if (type === 'mensuel') {
        dd.value = fmt(today).substr(0,7)+'-01'; df.value = fmt(today);
    } else if (type === 'annuel') {
        dd.value = today.getFullYear()+'-01-01'; df.value = today.getFullYear()+'-12-31';
    }
}
// Init
var selType = document.getElementById('typeRapport');
if (selType) updateRapportDates(selType.value);
</script>

<?php
function statutBadgePV(string $statut): string {
    $map = [
        'recu'                     => ['bg-secondary','Reçu'],
        'en_traitement'            => ['bg-warning text-dark','En traitement'],
        'classe'                   => ['bg-dark','Classé'],
        'transfere_instruction'    => ['bg-info text-dark','→ Instruction'],
        'transfere_jugement_direct'=> ['bg-success','→ Audience'],
    ];
    [$cls,$lbl] = $map[$statut] ?? ['bg-secondary',$statut];
    return "<span class=\"badge {$cls}\">{$lbl}</span>";
}
?>
