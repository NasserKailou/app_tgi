<?php
/**
 * RapportController — Génération de rapports de bord (fin de journée, périodiques)
 * Routes :
 *   POST /rapports/generer  → generer()
 *   GET  /rapports          → index()
 *   GET  /rapports/show/{id} → show()
 */
class RapportController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $user  = Auth::currentUser();
        $flash = $this->getFlash();

        $rapports = [];
        try {
            $stmt = $this->db->prepare(
                "SELECT r.*, u.nom AS gen_nom, u.prenom AS gen_prenom
                 FROM rapports r
                 LEFT JOIN users u ON r.genere_par = u.id
                 ORDER BY r.created_at DESC LIMIT 50"
            );
            $stmt->execute();
            $rapports = $stmt->fetchAll();
        } catch (\Exception $e) {}

        $this->view('rapports/index', compact('rapports','user','flash'));
    }

    public function generer(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','substitut_procureur','president']);
        CSRF::check();

        $type      = $_POST['type'] ?? 'quotidien';
        $titre     = $this->sanitize($_POST['titre'] ?? '');
        $dateDebut = $_POST['date_debut'] ?? date('Y-m-d');
        $dateFin   = $_POST['date_fin']   ?? date('Y-m-d');

        // Ajuster dates selon le type
        switch ($type) {
            case 'quotidien':
                $dateDebut = $dateFin = date('Y-m-d');
                break;
            case 'hebdomadaire':
                $dateDebut = date('Y-m-d', strtotime('monday this week'));
                $dateFin   = date('Y-m-d');
                break;
            case 'mensuel':
                $dateDebut = date('Y-m-01');
                $dateFin   = date('Y-m-d');
                break;
        }

        if (!$titre) {
            $typeLabels = ['quotidien'=>'Quotidien','hebdomadaire'=>'Hebdomadaire','mensuel'=>'Mensuel','personnalise'=>'Personnalisé'];
            $titre = 'Rapport ' . ($typeLabels[$type] ?? $type) . ' — ' . date('d/m/Y');
        }

        // Collecter les statistiques pour la période
        $data = $this->collectData($dateDebut, $dateFin);
        $data['titre']      = $titre;
        $data['type']       = $type;
        $data['date_debut'] = $dateDebut;
        $data['date_fin']   = $dateFin;
        $data['genere_par'] = Auth::currentUser();
        $data['date_generation'] = date('Y-m-d H:i:s');

        // Enregistrer en base
        $rapportId = null;
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO rapports (type, titre, date_debut, date_fin, contenu_json, genere_par)
                 VALUES (?,?,?,?,?,?)"
            );
            $stmt->execute([$type, $titre, $dateDebut, $dateFin, json_encode($data), Auth::userId()]);
            $rapportId = (int)$this->db->lastInsertId();
        } catch (\Exception $e) {}

        // Afficher le rapport HTML (imprimable)
        header('Content-Type: text/html; charset=utf-8');
        echo $this->renderRapportHTML($data, $rapportId);
        exit;
    }

    private function collectData(string $debut, string $fin): array
    {
        $params = [':debut' => $debut, ':fin' => $fin];

        $pvTotal = (int)$this->db->prepare(
            "SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN :debut AND :fin"
        )->execute($params) ? $this->db->prepare(
            "SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN :debut AND :fin"
        )->execute($params) || 0 : 0;

        // Méthode propre
        $q = fn(string $sql, array $p = []) => $this->safeQuery($sql, $p);

        $pvTotal       = $q("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN :debut AND :fin", $params);
        $pvNouveaux    = $q("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN :debut AND :fin AND statut='recu'", $params);
        $pvTraitement  = $q("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN :debut AND :fin AND statut='en_traitement'", $params);
        $pvClasses     = $q("SELECT COUNT(*) FROM pv WHERE date_classement BETWEEN :debut AND :fin", $params);
        $pvTransferes  = $q("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN :debut AND :fin AND statut IN ('transfere_instruction','transfere_jugement_direct')", $params);
        $pvAntiT       = $q("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN :debut AND :fin AND est_antiterroriste=1", $params);
        $mecTotal      = $q("SELECT COUNT(*) FROM mises_en_cause WHERE created_at BETWEEN :debut AND :fin", ['debut'=>$debut.' 00:00:00','fin'=>$fin.' 23:59:59']);
        $mecPoursuivis = $q("SELECT COUNT(*) FROM mises_en_cause WHERE date_decision BETWEEN :debut AND :fin AND decision_substitut='poursuivi'", $params);

        // PV par type
        $stmt = $this->db->prepare("SELECT type_affaire, COUNT(*) as nb FROM pv WHERE date_reception BETWEEN :debut AND :fin GROUP BY type_affaire");
        $stmt->execute($params);
        $pvParType = $stmt->fetchAll();

        // Derniers PV
        $stmt2 = $this->db->prepare(
            "SELECT p.numero_rg, p.numero_pv, p.date_reception, p.statut, p.type_affaire,
                    ue.nom as unite_nom
             FROM pv p LEFT JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
             WHERE p.date_reception BETWEEN :debut AND :fin
             ORDER BY p.date_reception DESC LIMIT 20"
        );
        $stmt2->execute($params);
        $pvListe = $stmt2->fetchAll();

        // Paramètres tribunal
        $params2 = $this->db->query("SELECT * FROM parametres_tribunal LIMIT 1")->fetch() ?: [];

        return compact(
            'pvTotal','pvNouveaux','pvTraitement','pvClasses','pvTransferes',
            'pvAntiT','mecTotal','mecPoursuivis','pvParType','pvListe','params2'
        );
    }

    private function safeQuery(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function renderRapportHTML(array $data, ?int $rapportId): string
    {
        $tribunal = $data['params2']['nom_tribunal'] ?? APP_FULL_NAME ?? 'TGI Hors Classe de Niamey';
        $titre    = htmlspecialchars($data['titre'] ?? 'Rapport');
        $debut    = date('d/m/Y', strtotime($data['date_debut']));
        $fin      = date('d/m/Y', strtotime($data['date_fin']));
        $genDate  = date('d/m/Y à H:i', strtotime($data['date_generation']));
        $genPar   = htmlspecialchars(($data['genere_par']['prenom'] ?? '') . ' ' . ($data['genere_par']['nom'] ?? ''));

        $typeColors = ['penale'=>'#dc3545','civile'=>'#0d6efd','commerciale'=>'#198754'];
        $pvTypesRows = '';
        foreach ($data['pvParType'] as $t) {
            $color = $typeColors[$t['type_affaire']] ?? '#6c757d';
            $pvTypesRows .= "<tr><td>".ucfirst($t['type_affaire'])."</td><td style='color:$color;font-weight:bold'>{$t['nb']}</td></tr>";
        }

        $pvRows = '';
        foreach ($data['pvListe'] as $pv) {
            $pvRows .= "<tr>
                <td>".htmlspecialchars($pv['numero_rg'])."</td>
                <td>".htmlspecialchars($pv['numero_pv'])."</td>
                <td>".date('d/m/Y', strtotime($pv['date_reception']))."</td>
                <td>".ucfirst($pv['type_affaire'])."</td>
                <td>".htmlspecialchars($pv['unite_nom'] ?? '—')."</td>
                <td>".htmlspecialchars($pv['statut'])."</td>
            </tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{$titre}</title>
<style>
body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; margin: 20px; color: #212529; }
h1 { font-size: 18px; color: #0d6efd; margin-bottom: 4px; }
h2 { font-size: 14px; color: #495057; margin: 16px 0 8px; border-bottom: 2px solid #dee2e6; padding-bottom: 4px; }
.header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 12px; margin-bottom: 16px; }
.stats-grid { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.stat-box { border: 1px solid #dee2e6; border-radius: 6px; padding: 10px 16px; min-width: 130px; text-align: center; }
.stat-val { font-size: 24px; font-weight: bold; color: #0d6efd; }
.stat-lbl { font-size: 10px; color: #6c757d; text-transform: uppercase; }
table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 11px; }
th { background: #f8f9fa; border: 1px solid #dee2e6; padding: 6px 8px; text-align: left; }
td { border: 1px solid #dee2e6; padding: 5px 8px; }
.footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #dee2e6; font-size: 10px; color: #adb5bd; }
@media print { body { margin: 0; } .no-print { display: none; } }
</style>
</head>
<body>
<div class="no-print" style="background:#f8f9fa;padding:10px;margin-bottom:16px;border-radius:6px;">
    <button onclick="window.print()" style="background:#0d6efd;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;margin-right:8px;">🖨 Imprimer</button>
    <button onclick="window.close()" style="background:#6c757d;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">✖ Fermer</button>
    <span style="color:#6c757d;margin-left:12px;font-size:11px;">Rapport #{$rapportId}</span>
</div>
<div class="header">
    <div style="font-size:11px;text-transform:uppercase;color:#6c757d;">République du Niger — Ministère de la Justice</div>
    <div style="font-size:14px;font-weight:bold;">{$tribunal}</div>
    <h1>{$titre}</h1>
    <div style="color:#6c757d;font-size:11px;">Période : du {$debut} au {$fin} — Généré le {$genDate} par {$genPar}</div>
</div>

<h2>📊 Statistiques de la période</h2>
<div class="stats-grid">
    <div class="stat-box"><div class="stat-val">{$data['pvTotal']}</div><div class="stat-lbl">PV reçus</div></div>
    <div class="stat-box"><div class="stat-val">{$data['pvNouveaux']}</div><div class="stat-lbl">Nouveaux</div></div>
    <div class="stat-box"><div class="stat-val">{$data['pvClasses']}</div><div class="stat-lbl">Classés</div></div>
    <div class="stat-box"><div class="stat-val">{$data['pvTransferes']}</div><div class="stat-lbl">Transférés</div></div>
    <div class="stat-box"><div class="stat-val">{$data['pvAntiT']}</div><div class="stat-lbl">Anti-terroriste</div></div>
    <div class="stat-box"><div class="stat-val">{$data['mecTotal']}</div><div class="stat-lbl">Mises en cause</div></div>
    <div class="stat-box"><div class="stat-val">{$data['mecPoursuivis']}</div><div class="stat-lbl">MEC poursuivis</div></div>
</div>

<h2>📁 Répartition par type d'affaire</h2>
<table>
    <thead><tr><th>Type</th><th>Nombre</th></tr></thead>
    <tbody>{$pvTypesRows}</tbody>
</table>

<h2>📋 Liste des PV de la période</h2>
<table>
    <thead><tr><th>N° RG</th><th>N° PV</th><th>Date réception</th><th>Type</th><th>Unité</th><th>Statut</th></tr></thead>
    <tbody>{$pvRows}</tbody>
</table>

<div class="footer">
    TGI Hors Classe de Niamey — Parquet du Procureur de la République — Document confidentiel généré automatiquement
    <?php if ($rapportId): ?>— Rapport #{$rapportId} archivé dans le système<?php endif; ?>
</div>
</body>
</html>
HTML;
    }
}
