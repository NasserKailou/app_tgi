<?php $pageTitle = 'Tableau de bord analytique'; ?>
<style>
.bg-purple   { background:#6f42c1 !important; }
.text-purple { color:#6f42c1 !important; }
.analytics-card { border-radius:14px; transition:box-shadow .2s; }
.analytics-card:hover { box-shadow:0 8px 28px rgba(0,0,0,.13); }
.kpi-icon { width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem; }
.chart-container { position:relative; }
.section-title { font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#6c757d;font-weight:700; }
.table-analytics th { background:#1a3c5e;color:#fff;font-size:.75rem;white-space:nowrap; }
.table-analytics td { font-size:.8rem;vertical-align:middle; }
.evolution-badge { font-size:.75rem;padding:.3em .6em; }
</style>

<?php
$moisNoms = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
$statLabels = [
    'nouveau'=>'Nouveau','recu'=>'Reçu','en_traitement'=>'En traitement',
    'classe'=>'Classé','transfere'=>'Transféré',
    'transfere_instruction'=>'Instruction','transfere_jugement_direct'=>'Audience dir.',
];
$statColors = [
    'nouveau'=>'#adb5bd','recu'=>'#0dcaf0','en_traitement'=>'#ffc107',
    'classe'=>'#212529','transfere'=>'#0d6efd',
    'transfere_instruction'=>'#198754','transfere_jugement_direct'=>'#20c997',
];
$mpColors = [
    'RI'=>'#0d6efd','CD'=>'#0dcaf0','FD'=>'#dc3545',
    'CRPC'=>'#6f42c1','autre'=>'#6c757d','Non défini'=>'#dee2e6',
];
?>

<!-- ══ En-tête ══ -->
<div class="mb-4 mt-2 d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-graph-up-arrow me-2" style="color:#6f42c1;"></i>Tableau de bord analytique
        </h4>
        <p class="text-muted mb-0 small">Vue complète — PVs, modes de poursuite, substituts, infractions, unités d'enquête</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <a href="<?= BASE_URL ?>/situation/pv" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-table me-1"></i>Situation détaillée
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i>Imprimer
        </button>
    </div>
</div>

<!-- ══ Filtres ══ -->
<div class="card border-0 shadow-sm mb-4" style="border-left:4px solid #6f42c1 !important;">
    <div class="card-body py-2">
        <form method="GET" action="<?= BASE_URL ?>/analytics" class="d-flex flex-wrap gap-3 align-items-end">
            <div>
                <label class="form-label small fw-semibold mb-1">Année</label>
                <select name="annee" class="form-select form-select-sm" style="width:90px;">
                    <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                    <option value="<?= $y ?>" <?= $annee === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold mb-1">Mois début</label>
                <select name="mois_debut" class="form-select form-select-sm" style="width:100px;">
                    <?php foreach ($moisNoms as $i => $mn): ?>
                    <option value="<?= $i+1 ?>" <?= $moisDebut === ($i+1) ? 'selected' : '' ?>><?= $mn ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold mb-1">Mois fin</label>
                <select name="mois_fin" class="form-select form-select-sm" style="width:100px;">
                    <?php foreach ($moisNoms as $i => $mn): ?>
                    <option value="<?= $i+1 ?>" <?= $moisFin === ($i+1) ? 'selected' : '' ?>><?= $mn ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Actualiser
                </button>
            </div>
            <div class="ms-auto small text-muted align-self-center">
                Période : <strong><?= date('d/m/Y', strtotime($dd)) ?></strong> → <strong><?= date('d/m/Y', strtotime($df)) ?></strong>
            </div>
        </form>
    </div>
</div>

<!-- ══ KPIs principaux ══ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="kpi-icon" style="background:#e8eef4;"><i class="bi bi-files text-primary"></i></div>
                <div>
                    <div class="fs-2 fw-bold lh-1" style="color:#1a3c5e;"><?= $totPVPeriode ?></div>
                    <div class="small text-muted">PVs période</div>
                    <?php if ($evolution !== null): ?>
                    <span class="badge evolution-badge <?= $evolution >= 0 ? 'bg-danger' : 'bg-success' ?>">
                        <?= $evolution >= 0 ? '+' : '' ?><?= $evolution ?>% vs <?= $anneeN1 ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="kpi-icon" style="background:#fff3cd;"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div>
                    <div class="fs-2 fw-bold lh-1 text-warning"><?= $totEnCours ?></div>
                    <div class="small text-muted">En cours</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="kpi-icon" style="background:#d1e7dd;"><i class="bi bi-send-check text-success"></i></div>
                <div>
                    <div class="fs-2 fw-bold lh-1 text-success"><?= $totTransfere ?></div>
                    <div class="small text-muted">Transférés</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="kpi-icon" style="background:#f8d7da;"><i class="bi bi-archive text-danger"></i></div>
                <div>
                    <div class="fs-2 fw-bold lh-1 text-danger"><?= $totClasse ?></div>
                    <div class="small text-muted">Classés</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="kpi-icon" style="background:#e8d5f5;"><i class="bi bi-file-earmark-text text-purple"></i></div>
                <div>
                    <div class="fs-2 fw-bold lh-1 text-purple"><?= $crpcStats['total'] ?></div>
                    <div class="small text-muted">Dossiers CRPC</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="kpi-icon" style="background:#fff8cd;"><i class="bi bi-shield-exclamation" style="color:#fd7e14;"></i></div>
                <div>
                    <div class="fs-2 fw-bold lh-1" style="color:#fd7e14;"><?= $antiterro['antiterro'] ?></div>
                    <div class="small text-muted">Antiterroristes</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══ Ligne 1 : Évolution + Statuts ══ -->
<div class="row g-3 mb-4">
    <!-- Évolution mensuelle -->
    <div class="col-lg-8">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <span class="fw-semibold small"><i class="bi bi-graph-up me-2 text-primary"></i>Évolution mensuelle des PVs — <?= $annee ?></span>
                <span class="section-title">Taux résolution</span>
            </div>
            <div class="card-body chart-container" style="height:280px;">
                <canvas id="chartEvolution"></canvas>
            </div>
        </div>
    </div>
    <!-- Répartition statuts -->
    <div class="col-lg-4">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <span class="fw-semibold small"><i class="bi bi-pie-chart me-2 text-success"></i>Répartition par statut</span>
            </div>
            <div class="card-body chart-container d-flex align-items-center justify-content-center" style="height:280px;">
                <canvas id="chartStatut"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ══ Ligne 2 : Modes poursuite + Unités ══ -->
<div class="row g-3 mb-4">
    <!-- Modes de poursuite -->
    <div class="col-lg-5">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <span class="fw-semibold small"><i class="bi bi-diagram-3 me-2 text-info"></i>Modes de poursuite</span>
            </div>
            <div class="card-body chart-container d-flex align-items-center justify-content-center" style="height:280px;">
                <canvas id="chartMode"></canvas>
            </div>
        </div>
    </div>
    <!-- Unités d'enquête -->
    <div class="col-lg-7">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <span class="fw-semibold small"><i class="bi bi-building me-2" style="color:#1a3c5e;"></i>Top unités d'enquête</span>
            </div>
            <div class="card-body chart-container" style="height:280px;">
                <canvas id="chartUnites"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ══ Ligne 3 : Top infractions ══ -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <span class="fw-semibold small"><i class="bi bi-gavel me-2 text-danger"></i>Top 10 infractions (infraction principale)</span>
            </div>
            <div class="card-body chart-container" style="height:280px;">
                <canvas id="chartInfractions"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <span class="fw-semibold small"><i class="bi bi-list-check me-2 text-warning"></i>Top 10 infractions qualifiées (pv_infractions)</span>
            </div>
            <div class="card-body chart-container" style="height:280px;">
                <canvas id="chartInfractionsMulti"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ══ Ligne 4 : Substituts ══ -->
<div class="row g-3 mb-4">
    <!-- Charge substituts (barres empilées) -->
    <div class="col-lg-8">
        <div class="card analytics-card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <span class="fw-semibold small"><i class="bi bi-person-badge me-2 text-success"></i>Charge des substituts du procureur</span>
            </div>
            <div class="card-body chart-container" style="height:320px;">
                <canvas id="chartSubstituts"></canvas>
            </div>
        </div>
    </div>
    <!-- CRPC + Antiterro -->
    <div class="col-lg-4">
        <div class="card analytics-card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <span class="fw-semibold small"><i class="bi bi-file-earmark-text me-2 text-purple"></i>Dossiers CRPC</span>
            </div>
            <div class="card-body chart-container d-flex align-items-center justify-content-center" style="height:150px;">
                <canvas id="chartCRPC"></canvas>
            </div>
        </div>
        <div class="card analytics-card border-0 shadow-sm">
            <div class="card-header bg-white">
                <span class="fw-semibold small"><i class="bi bi-shield-exclamation me-2" style="color:#fd7e14;"></i>Antiterroriste vs Droit Commun</span>
            </div>
            <div class="card-body chart-container d-flex align-items-center justify-content-center" style="height:130px;">
                <canvas id="chartAntiterro"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ══ Tableau charge substituts ══ -->
<?php if (!empty($chargeSubstituts)): ?>
<div class="card analytics-card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold small">
        <i class="bi bi-table me-2 text-primary"></i>Tableau récapitulatif — Charge par substitut
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 table-analytics">
            <thead>
                <tr>
                    <th>Substitut</th>
                    <th class="text-center">Total PVs</th>
                    <th class="text-center">En cours</th>
                    <th class="text-center">Transférés</th>
                    <th class="text-center">Classés</th>
                    <th class="text-center">% résolution</th>
                    <th>Progression</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($chargeSubstituts as $cs):
                $tot = (int)$cs['total'];
                $res = (int)$cs['transfere'] + (int)$cs['classe'];
                $pct = $tot > 0 ? round($res/$tot*100) : 0;
            ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($cs['sub']) ?></td>
                <td class="text-center"><span class="badge bg-primary"><?= $tot ?></span></td>
                <td class="text-center"><span class="badge bg-warning text-dark"><?= $cs['en_cours'] ?></span></td>
                <td class="text-center"><span class="badge bg-success"><?= $cs['transfere'] ?></span></td>
                <td class="text-center"><span class="badge bg-dark"><?= $cs['classe'] ?></span></td>
                <td class="text-center fw-bold <?= $pct >= 70 ? 'text-success' : ($pct >= 40 ? 'text-warning' : 'text-danger') ?>">
                    <?= $pct ?>%
                </td>
                <td style="min-width:120px;">
                    <div class="progress" style="height:10px;border-radius:6px;">
                        <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ══ Tableau délais ══ -->
<?php if (!empty($delaisSubstituts)): ?>
<div class="card analytics-card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold small">
        <i class="bi bi-clock-history me-2 text-warning"></i>Délai moyen d'affectation par substitut (jours)
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 table-analytics">
            <thead><tr><th>Substitut</th><th class="text-center">Délai moyen (j.)</th><th>Indicateur</th></tr></thead>
            <tbody>
            <?php foreach ($delaisSubstituts as $d): ?>
            <?php $del = (float)$d['delai_moy']; ?>
            <tr>
                <td><?= htmlspecialchars($d['sub']) ?></td>
                <td class="text-center fw-bold <?= $del <= 2 ? 'text-success' : ($del <= 7 ? 'text-warning' : 'text-danger') ?>">
                    <?= $del ?> j
                </td>
                <td>
                    <?php if ($del <= 2): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Rapide</span>
                    <?php elseif ($del <= 7): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Acceptable</span>
                    <?php else: ?>
                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Lent</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ══ Données JSON pour Chart.js ══ -->
<script>
// Données PHP → JS
const pvParMois     = <?= json_encode(array_values($pvParMois)) ?>;
const tauxReso      = <?= json_encode(array_values($tauxResolution)) ?>;
const moisLabels    = <?= json_encode($moisNoms) ?>;
const pvStatutKeys  = <?= json_encode(array_keys($pvParStatut)) ?>;
const pvStatutVals  = <?= json_encode(array_values($pvParStatut)) ?>;
const pvStatutClrs  = <?= json_encode(array_values(array_map(fn($k) => $statColors[$k] ?? '#adb5bd', array_keys($pvParStatut)))) ?>;
const pvStatutLbls  = <?= json_encode(array_values(array_map(fn($k) => $statLabels[$k] ?? $k, array_keys($pvParStatut)))) ?>;
const pvModeKeys    = <?= json_encode(array_keys($pvParMode)) ?>;
const pvModeVals    = <?= json_encode(array_values($pvParMode)) ?>;
const pvModeClrs    = <?= json_encode(array_values(array_map(fn($k) => $mpColors[$k] ?? '#adb5bd', array_keys($pvParMode)))) ?>;
const topInfLabels  = <?= json_encode(array_column($topInfractions,'code')) ?>;
const topInfVals    = <?= json_encode(array_column($topInfractions,'n')) ?>;
const topInfLblsFull= <?= json_encode(array_map(fn($r)=>$r['code'].': '.$r['libelle'], $topInfractions)) ?>;
const topInfMLabels = <?= json_encode(array_column($topInfractionsMulti,'code')) ?>;
const topInfMVals   = <?= json_encode(array_column($topInfractionsMulti,'n')) ?>;
const topInfMLblsFull=<?= json_encode(array_map(fn($r)=>$r['code'].': '.$r['libelle'], $topInfractionsMulti)) ?>;
const unitesLabels  = <?= json_encode(array_column($pvParUnite,'unite')) ?>;
const unitesVals    = <?= json_encode(array_column($pvParUnite,'n')) ?>;
const subLabels     = <?= json_encode(array_column($chargeSubstituts,'sub')) ?>;
const subTotal      = <?= json_encode(array_column($chargeSubstituts,'total')) ?>;
const subTransfere  = <?= json_encode(array_column($chargeSubstituts,'transfere')) ?>;
const subClasse     = <?= json_encode(array_column($chargeSubstituts,'classe')) ?>;
const subEnCours    = <?= json_encode(array_column($chargeSubstituts,'en_cours')) ?>;
const crpcData      = <?= json_encode([$crpcStats['homologuee'],$crpcStats['refusee'],$crpcStats['en_cours']]) ?>;
const antiterroData = <?= json_encode([$antiterro['standard'],$antiterro['antiterro']]) ?>;

// Palette cohérente navy → turquoise
const navyPalette = [
    '#1a3c5e','#2563a8','#198754','#ffc107','#dc3545',
    '#0dcaf0','#6f42c1','#fd7e14','#20c997','#6c757d'
];

document.addEventListener('DOMContentLoaded', function() {

    // 1. Évolution mensuelle + taux résolution
    new Chart(document.getElementById('chartEvolution'), {
        data: {
            labels: moisLabels,
            datasets: [
                {
                    type:'bar',
                    label:'PVs reçus',
                    data: pvParMois,
                    backgroundColor:'rgba(26,60,94,.7)',
                    borderColor:'#1a3c5e',
                    borderWidth:1,
                    yAxisID:'y',
                    order:2,
                },
                {
                    type:'line',
                    label:'Taux résolution (%)',
                    data: tauxReso,
                    borderColor:'#198754',
                    backgroundColor:'rgba(25,135,84,.1)',
                    pointBackgroundColor:'#198754',
                    borderWidth:2.5,
                    fill:true,
                    tension:.35,
                    yAxisID:'y1',
                    order:1,
                }
            ]
        },
        options:{
            responsive:true, maintainAspectRatio:false,
            interaction:{mode:'index',intersect:false},
            plugins:{legend:{position:'top',labels:{font:{size:11}}},
                     tooltip:{bodyFont:{size:11},titleFont:{size:11}}},
            scales:{
                y:{beginAtZero:true,grid:{color:'rgba(0,0,0,.05)'},
                   title:{display:true,text:'Nombre de PVs',font:{size:10}},
                   ticks:{font:{size:10}}},
                y1:{position:'right',beginAtZero:true,max:100,
                    grid:{drawOnChartArea:false},
                    title:{display:true,text:'Taux résolution %',font:{size:10}},
                    ticks:{callback:v=>v+'%',font:{size:10}}}
            }
        }
    });

    // 2. Répartition statuts (donut)
    new Chart(document.getElementById('chartStatut'), {
        type:'doughnut',
        data:{labels:pvStatutLbls, datasets:[{data:pvStatutVals,
            backgroundColor:pvStatutClrs,borderWidth:2,hoverOffset:6}]},
        options:{
            responsive:true,maintainAspectRatio:false,
            cutout:'60%',
            plugins:{legend:{position:'bottom',labels:{font:{size:10},boxWidth:12}},
                     tooltip:{callbacks:{label:function(c){
                         const tot=c.dataset.data.reduce((a,b)=>a+b,0);
                         return c.label+': '+c.parsed+' ('+Math.round(c.parsed/tot*100)+'%)';
                     }}}}
        }
    });

    // 3. Modes de poursuite (pie)
    new Chart(document.getElementById('chartMode'), {
        type:'pie',
        data:{labels:pvModeKeys, datasets:[{data:pvModeVals,
            backgroundColor:pvModeClrs,borderWidth:2,hoverOffset:6}]},
        options:{
            responsive:true,maintainAspectRatio:false,
            plugins:{legend:{position:'right',labels:{font:{size:11},boxWidth:14}},
                     tooltip:{callbacks:{label:function(c){
                         const tot=c.dataset.data.reduce((a,b)=>a+b,0);
                         return c.label+': '+c.parsed+' ('+Math.round(c.parsed/tot*100)+'%)';
                     }}}}
        }
    });

    // 4. Unités d'enquête (barres horizontales)
    new Chart(document.getElementById('chartUnites'), {
        type:'bar',
        data:{labels:unitesLabels, datasets:[{
            data:unitesVals,
            backgroundColor:unitesVals.map((_,i)=>navyPalette[i%navyPalette.length]),
            borderRadius:5,
        }]},
        options:{
            indexAxis:'y',responsive:true,maintainAspectRatio:false,
            plugins:{legend:{display:false},
                     tooltip:{callbacks:{label:c=>c.parsed.x+' PVs'}}},
            scales:{
                x:{beginAtZero:true,ticks:{font:{size:9}},grid:{color:'rgba(0,0,0,.05)'}},
                y:{ticks:{font:{size:10}}}
            }
        }
    });

    // 5. Top infractions (barres)
    new Chart(document.getElementById('chartInfractions'), {
        type:'bar',
        data:{labels:topInfLabels, datasets:[{
            label:'PVs',
            data:topInfVals,
            backgroundColor:'rgba(220,53,69,.75)',
            borderColor:'#dc3545',borderWidth:1,borderRadius:4,
        }]},
        options:{
            responsive:true,maintainAspectRatio:false,
            plugins:{legend:{display:false},
                     tooltip:{callbacks:{
                         title:i=>[topInfLblsFull[i[0].dataIndex]||i[0].label],
                         label:c=>c.parsed.y+' PVs'
                     }}},
            scales:{y:{beginAtZero:true,ticks:{font:{size:10}}},
                    x:{ticks:{font:{size:9}}}}
        }
    });

    // 6. Top infractions multi
    new Chart(document.getElementById('chartInfractionsMulti'), {
        type:'bar',
        data:{labels:topInfMLabels, datasets:[{
            label:'Occurrences',
            data:topInfMVals,
            backgroundColor:'rgba(255,193,7,.8)',
            borderColor:'#ffc107',borderWidth:1,borderRadius:4,
        }]},
        options:{
            responsive:true,maintainAspectRatio:false,
            plugins:{legend:{display:false},
                     tooltip:{callbacks:{
                         title:i=>[topInfMLblsFull[i[0].dataIndex]||i[0].label],
                         label:c=>c.parsed.y+' occurrences'
                     }}},
            scales:{y:{beginAtZero:true,ticks:{font:{size:10}}},
                    x:{ticks:{font:{size:9}}}}
        }
    });

    // 7. Charge substituts (barres empilées)
    if (subLabels.length > 0) {
        new Chart(document.getElementById('chartSubstituts'), {
            type:'bar',
            data:{
                labels:subLabels,
                datasets:[
                    {label:'En cours', data:subEnCours,  backgroundColor:'rgba(255,193,7,.8)'},
                    {label:'Transférés',data:subTransfere,backgroundColor:'rgba(25,135,84,.8)'},
                    {label:'Classés',   data:subClasse,   backgroundColor:'rgba(33,37,41,.7)'},
                ]
            },
            options:{
                responsive:true,maintainAspectRatio:false,
                plugins:{legend:{position:'top',labels:{font:{size:11}}}},
                scales:{
                    x:{stacked:true,ticks:{font:{size:10}}},
                    y:{stacked:true,beginAtZero:true,ticks:{font:{size:10}}}
                }
            }
        });
    }

    // 8. CRPC (donut)
    new Chart(document.getElementById('chartCRPC'), {
        type:'doughnut',
        data:{
            labels:['Homologuée','Refusée','En cours'],
            datasets:[{data:crpcData,
                backgroundColor:['#198754','#dc3545','#6f42c1'],
                borderWidth:2}]
        },
        options:{
            responsive:true,maintainAspectRatio:false,cutout:'55%',
            plugins:{legend:{position:'right',labels:{font:{size:10},boxWidth:10}}}
        }
    });

    // 9. Antiterro (donut)
    new Chart(document.getElementById('chartAntiterro'), {
        type:'doughnut',
        data:{
            labels:['Droit Commun','Antiterroriste'],
            datasets:[{data:antiterroData,
                backgroundColor:['#1a3c5e','#fd7e14'],
                borderWidth:2}]
        },
        options:{
            responsive:true,maintainAspectRatio:false,cutout:'55%',
            plugins:{legend:{position:'right',labels:{font:{size:10},boxWidth:10}}}
        }
    });

});
</script>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
